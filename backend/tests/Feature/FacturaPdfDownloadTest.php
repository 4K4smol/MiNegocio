<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaDocumento;
use App\Models\Servicio;
use App\Models\TipoCliente;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacturaPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    public function test_descarga_pdf_de_factura_emitida_y_registra_documento(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();
        $factura = $this->crearFacturaEmitida($user, $cliente);

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/v1/empresa/facturas/'.$factura->id.'/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('factura-A-'.now()->year.'-000001.pdf');

        $this->assertStringStartsWith('%PDF', $response->streamedContent());

        $documento = FacturaDocumento::query()->where('factura_id', $factura->id)->where('tipo', 'pdf')->first();
        $this->assertNotNull($documento);
        $this->assertSame('application/pdf', $documento->mime_type);
        Storage::disk('local')->assertExists($documento->ruta);
    }

    public function test_no_descarga_pdf_de_factura_en_borrador(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();
        $factura = $this->crearFacturaBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/empresa/facturas/'.$factura->id.'/pdf')
            ->assertStatus(422);

        $this->assertDatabaseMissing('factura_documentos', [
            'factura_id' => $factura->id,
            'tipo' => 'pdf',
        ]);
    }

    public function test_usuario_de_otra_empresa_no_puede_descargar_pdf(): void
    {
        [$userA, $clienteA] = $this->crearContextoEmpresa('A');
        [$userB] = $this->crearContextoEmpresa('B');
        $factura = $this->crearFacturaEmitida($userA, $clienteA);

        $this->actingAs($userB, 'sanctum')
            ->getJson('/api/v1/empresa/facturas/'.$factura->id.'/pdf')
            ->assertForbidden();

        $this->assertDatabaseMissing('factura_documentos', [
            'factura_id' => $factura->id,
            'tipo' => 'pdf',
        ]);
    }

    public function test_factura_generada_desde_orden_emitida_puede_descargarse_en_pdf(): void
    {
        [$user, $cliente, $servicio] = $this->crearContextoOrden();

        $ordenId = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/ordenes-trabajo', [
                'cliente_id' => $cliente->id,
                'lineas' => [[
                    'servicio_id' => $servicio->id,
                    'descripcion' => 'Servicio desde orden',
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                    'iva_porcentaje' => 21,
                    'facturable' => true,
                ]],
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/ordenes-trabajo/'.$ordenId.'/completar')
            ->assertOk()
            ->assertJsonPath('data.estado', 'completada');

        $facturaId = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/ordenes-trabajo/'.$ordenId.'/generar-factura', ['modo' => 'emitir'])
            ->assertCreated()
            ->assertJsonPath('data.estado_factura', 'emitida')
            ->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->get('/api/v1/empresa/facturas/'.$facturaId.'/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('factura-A-'.now()->year.'-000001.pdf');

        $this->assertDatabaseHas('factura_documentos', [
            'factura_id' => $facturaId,
            'tipo' => 'pdf',
        ]);
    }

    private function crearFacturaBorrador(User $user, Cliente $cliente): Factura
    {
        $id = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas', [
                'cliente_id' => $cliente->id,
                'tipo_factura_codigo' => 'ordinaria',
                'lineas' => [[
                    'descripcion' => 'Servicio de consultoria',
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertCreated()
            ->json('data.id');

        return Factura::query()->findOrFail($id);
    }

    private function crearFacturaEmitida(User $user, Cliente $cliente): Factura
    {
        $factura = $this->crearFacturaBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/'.$factura->id.'/emitir')
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'emitida');

        return $factura->fresh();
    }

    /**
     * @return array{0: User, 1: Cliente}
     */
    private function crearContextoEmpresa(string $sufijo = ''): array
    {
        $sufijo = $sufijo ?: uniqid('', true);

        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa PDF '.$sufijo,
            'nif' => 'B'.substr(str_pad((string) abs(crc32($sufijo.microtime())), 8, '0', STR_PAD_LEFT), 0, 8),
            'direccion_fiscal' => 'Calle PDF 1',
            'correo' => 'facturas@example.test',
            'telefono' => '600000000',
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'facturacion');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => TipoCliente::query()->where('codigo', 'particular')->value('id') ?? 1,
            'nombre' => 'Cliente PDF '.$sufijo,
            'dni_cif' => 'D'.substr(str_pad((string) abs(crc32('cliente'.$sufijo.microtime())), 8, '0', STR_PAD_LEFT), 0, 8),
            'direccion' => 'Calle Cliente 2',
            'activo' => true,
        ]);

        return [$user, $cliente];
    }

    /**
     * @return array{0: User, 1: Cliente, 2: Servicio}
     */
    private function crearContextoOrden(): array
    {
        [$user, $cliente] = $this->crearContextoEmpresa('ORD');
        app(ModuloService::class)->activarModulo((int) $user->empresa_id, 'ordenes');

        $servicio = Servicio::query()->create([
            'empresa_id' => $user->empresa_id,
            'codigo' => 'PDF-ORD',
            'nombre' => 'Servicio PDF orden',
            'unidad_servicio' => 'unidad',
            'duracion_estimada_min' => 30,
            'activo' => true,
        ]);

        return [$user, $cliente, $servicio];
    }
}

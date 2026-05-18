<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\TipoCliente;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacturacionNormalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_factura_en_borrador_se_puede_crear_y_editar(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();

        $facturaId = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas', [
                'cliente_id' => $cliente->id,
                'tipo_factura_codigo' => 'ordinaria',
                'lineas' => [[
                    'descripcion' => 'Servicio mensual',
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.estado_factura', 'borrador')
            ->assertJsonPath('data.numero', null)
            ->json('data.id');

        $this->assertDatabaseMissing('registros_facturacion', ['factura_id' => $facturaId]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/facturas/' . $facturaId, [
                'lineas' => [[
                    'descripcion' => 'Servicio mensual ajustado',
                    'cantidad' => 2,
                    'precio_unitario' => 50,
                    'iva_porcentaje' => 10,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.total', 110);
    }

    public function test_emitir_factura_asigna_numero_por_secuencia_y_genera_registro_alta(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();
        $factura = $this->crearBorrador($user, $cliente);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/' . $factura->id . '/emitir')
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'emitida');

        $this->assertNotNull($response->json('data.numero'));
        $this->assertNotNull($response->json('data.numero_completo'));

        $this->assertDatabaseHas('secuencias_facturacion', [
            'empresa_id' => $user->empresa_id,
            'serie' => 'A',
            'ultimo_numero' => 1,
        ]);

        $this->assertDatabaseHas('registros_facturacion', [
            'factura_id' => $factura->id,
        ]);
    }

    public function test_documentos_no_fiscales_no_generan_registro_verifactu(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();

        $facturaId = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas', [
                'cliente_id' => $cliente->id,
                'tipo_factura_codigo' => 'proforma',
                'lineas' => [[
                    'descripcion' => 'Proforma',
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                ]],
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/' . $facturaId . '/emitir')
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'enviada')
            ->assertJsonPath('data.numero', null);

        $this->assertDatabaseMissing('registros_facturacion', ['factura_id' => $facturaId]);
    }

    public function test_registrar_cobro_actualiza_estado_comercial_sin_crear_registro_tecnico(): void
    {
        [$user, $cliente] = $this->crearContextoEmpresa();
        $factura = $this->crearBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/' . $factura->id . '/emitir')
            ->assertOk();

        $registrosAntes = $factura->registrosFacturacion()->count();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/' . $factura->id . '/cobros', [
                'importe' => 50,
                'metodo_pago' => 'transferencia',
            ])
            ->assertCreated()
            ->assertJsonPath('data.estado_factura', 'pagada_parcial');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/' . $factura->id . '/cobros', [
                'importe' => 71,
                'metodo_pago' => 'transferencia',
            ])
            ->assertCreated()
            ->assertJsonPath('data.estado_factura', 'pagada');

        $this->assertSame($registrosAntes, $factura->fresh()->registrosFacturacion()->count());
        $this->assertDatabaseHas('factura_historial', [
            'factura_id' => $factura->id,
            'accion' => 'cobro_registrado',
        ]);
    }

    public function test_empresa_no_puede_acceder_a_facturas_de_otra_empresa(): void
    {
        [$userA, $clienteA] = $this->crearContextoEmpresa('A');
        [$userB] = $this->crearContextoEmpresa('B');
        $factura = $this->crearBorrador($userA, $clienteA);

        $this->actingAs($userB, 'sanctum')
            ->getJson('/api/v1/facturas/' . $factura->id)
            ->assertNotFound();
    }

    public function test_existe_tabla_de_secuencias_y_no_tabla_de_contadores_de_ordenes_para_facturas(): void
    {
        $this->assertTrue(Schema::hasTable('secuencias_facturacion'));
        $this->assertFalse(Schema::hasTable('orden_trabajo_contadores'));
    }

    private function crearBorrador(User $user, Cliente $cliente): Factura
    {
        $id = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas', [
                'cliente_id' => $cliente->id,
                'tipo_factura_codigo' => 'ordinaria',
                'lineas' => [[
                    'descripcion' => 'Servicio',
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertCreated()
            ->json('data.id');

        return Factura::query()->findOrFail($id);
    }

    /**
     * @return array{0:User,1:Cliente}
     */
    private function crearContextoEmpresa(string $sufijo = 'X'): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa Facturacion ' . $sufijo,
            'nif' => 'B' . substr(str_pad((string) abs(crc32($sufijo . microtime())), 8, '0', STR_PAD_LEFT), 0, 8),
            'direccion_fiscal' => 'Calle Fiscal 1',
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'facturacion');

        $tipoClienteId = TipoCliente::query()->where('codigo', 'particular')->value('id') ?? TipoCliente::query()->value('id');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => $tipoClienteId,
            'nombre' => 'Cliente ' . $sufijo,
            'dni_cif' => 'DNI' . substr(str_pad((string) abs(crc32('cliente' . $sufijo . microtime())), 8, '0', STR_PAD_LEFT), 0, 8),
            'activo' => true,
        ]);

        return [$user, $cliente];
    }
}

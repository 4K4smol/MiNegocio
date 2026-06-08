<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\RegistroFacturacion;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifactuClientFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_modo_simulado_marca_registros_como_enviados_simulado(): void
    {
        config(['services.verifactu.modo' => 'simulado']);

        [$user, $registro] = $this->crearRegistroPendiente('B30000001');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/verifactu/enviar-pendientes')
            ->assertOk()
            ->assertJsonPath('data.modo', 'simulado')
            ->assertJsonPath('data.enviados', 1);

        $registro->refresh();

        $this->assertSame('enviado_simulado', $registro->estado_remision);
        $this->assertNotNull($registro->enviado_aeat_at);
    }

    public function test_modo_real_sin_configurar_devuelve_conflict_y_no_modifica_registros(): void
    {
        config([
            'services.verifactu.modo' => 'real',
            'services.verifactu.endpoint' => null,
            'services.verifactu.cert_path' => null,
            'services.verifactu.cert_password' => null,
        ]);

        [$user, $registro] = $this->crearRegistroPendiente('B30000002');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/verifactu/enviar-pendientes')
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'La remisión real VeriFactu no está configurada. Use modo simulado o configure endpoint y certificado.'
            );

        $registro->refresh();

        $this->assertSame('pendiente', $registro->estado_remision);
        $this->assertNull($registro->enviado_aeat_at);
        $this->assertSame(0, $registro->intentos_remision);
    }

    /**
     * @return array{0: User, 1: RegistroFacturacion}
     */
    private function crearRegistroPendiente(string $nif): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa '.$nif,
            'nif' => $nif,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id' => 2,
        ]);

        app(ModuloService::class)->activarModulo((int) $empresa->id, 'facturacion');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => 1,
            'nombre' => 'Cliente '.$nif,
            'dni_cif' => 'C'.$nif,
            'activo' => true,
        ]);

        $factura = Factura::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_factura_id' => 1,
            'estado_factura_id' => 2,
            'serie' => 'A',
            'numero' => 'F-'.$nif,
            'fecha_emision' => now()->toDateString(),
            'fecha_operacion' => now()->toDateString(),
            'emisor_nif' => $nif,
            'emisor_nombre_razon_social' => 'Empresa '.$nif,
            'emisor_domicilio_fiscal' => 'N/D',
            'receptor_nif' => $cliente->dni_cif,
            'receptor_nombre_razon_social' => $cliente->nombre,
            'receptor_domicilio_fiscal' => 'N/D',
            'subtotal' => 100,
            'cuota_iva' => 21,
            'total' => 121,
        ]);

        $registro = RegistroFacturacion::query()->create([
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => 1,
            'modo_remision_facturacion_id' => 1,
            'empresa_id' => $empresa->id,
            'emisor_nif' => $nif,
            'emisor_nombre_razon_social' => 'Empresa '.$nif,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => now()->toDateString(),
            'tipo_factura_id' => 1,
            'cuota_total' => 21,
            'importe_total' => 121,
            'primer_registro_cadena' => true,
            'tipo_huella' => 'sha256',
            'hash_actual' => str_repeat('b', 64),
            'generado_at' => now(),
            'xml_contenido' => '<Registro />',
            'xml_version' => '1.0',
            'codigo_sistema_informatico' => 'TEST',
            'nombre_sistema' => 'MiNegocio',
            'version_sistema' => '1.0.0',
            'numero_instalacion' => 'EMP-'.$empresa->id,
            'productor_nif' => $nif,
            'productor_nombre' => 'MiNegocio',
            'estado_remision' => 'pendiente',
            'intentos_remision' => 0,
        ]);

        return [$user, $registro];
    }
}

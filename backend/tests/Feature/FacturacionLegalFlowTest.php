<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoLinea;
use App\Models\RegistroEventoFacturacion;
use App\Models\RegistroFacturacion;
use App\Models\User;
use App\Services\FacturacionDesdeOrdenService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class FacturacionLegalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_se_puede_generar_factura_si_orden_no_esta_completada(): void
    {
        [$user, $orden] = $this->crearContextoOrden('pendiente', 'OT-001');

        $this->expectException(RuntimeException::class);
        app(FacturacionDesdeOrdenService::class)->generarDesdeOrden($orden->fresh(['estado', 'lineas', 'empresa', 'cliente.localizacionPrincipal']), $user);
    }

    public function test_orden_completada_genera_factura_y_registro_alta_y_eventos(): void
    {
        [$user, $orden] = $this->crearContextoOrden('completada', 'OT-002');

        $factura = app(FacturacionDesdeOrdenService::class)->generarDesdeOrden($orden->fresh(['estado', 'lineas', 'empresa', 'cliente.localizacionPrincipal']), $user);

        $this->assertDatabaseHas('facturas', ['id' => $factura->id]);
        $this->assertDatabaseHas('registros_facturacion', ['factura_id' => $factura->id]);
        $this->assertDatabaseHas('registros_evento_facturacion', ['factura_id' => $factura->id, 'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_CREADO']);
    }

    public function test_dos_facturas_misma_empresa_quedan_encadenadas(): void
    {
        [$user, $orden1] = $this->crearContextoOrden('completada', 'OT-003');
        [$__, $orden2] = $this->crearContextoOrden('completada', 'OT-004', $user->empresa_id);

        app(FacturacionDesdeOrdenService::class)->generarDesdeOrden($orden1->fresh(['estado', 'lineas', 'empresa', 'cliente.localizacionPrincipal']), $user);
        $factura2 = app(FacturacionDesdeOrdenService::class)->generarDesdeOrden($orden2->fresh(['estado', 'lineas', 'empresa', 'cliente.localizacionPrincipal']), $user);

        $registros = RegistroFacturacion::query()->where('empresa_id', $user->empresa_id)->orderBy('id')->get();
        $this->assertCount(2, $registros);
        $this->assertSame($registros[0]->hash_actual, $registros[1]->registro_anterior_hash_64);
        $this->assertSame($factura2->id, $registros[1]->factura_id);
    }

    public function test_anular_factura_genera_registro_anulacion_y_no_permita_doble_anulacion(): void
    {
        [$user, $orden] = $this->crearContextoOrden('completada', 'OT-005');
        $factura = app(FacturacionDesdeOrdenService::class)->generarDesdeOrden($orden->fresh(['estado', 'lineas', 'empresa', 'cliente.localizacionPrincipal']), $user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/facturas/' . $factura->id . '/anular', ['motivo_anulacion' => 'Cliente solicita anulación'])->assertOk();

        $this->assertDatabaseHas('registros_facturacion', ['factura_id' => $factura->id, 'registro_anterior_numero' => $factura->numero]);
        $this->assertDatabaseHas('registros_evento_facturacion', ['factura_id' => $factura->id, 'codigo_evento' => 'REGISTRO_FACTURACION_ANULACION_CREADO']);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/facturas/' . $factura->id . '/anular')->assertStatus(500);
    }

    private function crearContextoOrden(string $estadoCodigo, string $numeroOrden, ?int $empresaId = null): array
    {
        $empresaId = $empresaId ?? Empresa::query()->create(['tipo_empresa_id' => 1, 'nombre_fiscal' => 'ACME SL', 'nif' => 'B12345678'])->id;
        $user = User::factory()->create(['empresa_id' => $empresaId]);

        $estadoId = $estadoCodigo === 'completada' ? 3 : 1;
        $orden = OrdenTrabajo::query()->create(['empresa_id' => $empresaId, 'cliente_id' => 1, 'numero' => $numeroOrden, 'estado_id' => $estadoId, 'estado_codigo' => $estadoCodigo]);

        OrdenTrabajoLinea::query()->create([
            'orden_trabajo_id' => $orden->id,
            'descripcion' => 'Servicio',
            'cantidad' => 1,
            'precio_unitario' => 100,
            'base_imponible' => 100,
            'iva_porcentaje' => 21,
            'cuota_iva' => 21,
            'total' => 121,
            'facturable' => true,
        ]);

        return [$user, $orden];
    }
}

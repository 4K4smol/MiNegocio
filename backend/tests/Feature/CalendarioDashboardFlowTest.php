<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CalendarioEvento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\RegistroFacturacion;
use App\Models\Servicio;
use App\Models\User;
use App\Services\ModuloService;
use App\Services\OrdenTrabajoService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioDashboardFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_una_orden_programada_crea_evento_de_calendario(): void
    {
        [$user, $cliente, $servicio] = $this->crearContextoEmpresa('B10000001');

        $orden = app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($cliente->id, $servicio->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
            'fecha_programada_fin' => now()->addDay()->addHour()->toDateTimeString(),
        ]), $user);

        $this->assertDatabaseHas('calendario_eventos', [
            'empresa_id' => $user->empresa_id,
            'orden_trabajo_id' => $orden->id,
            'cliente_id' => $cliente->id,
            'tipo' => 'ORDEN_TRABAJO',
            'estado_codigo' => 'programada',
        ]);
    }

    public function test_una_orden_actualizada_actualiza_su_evento(): void
    {
        [$user, $cliente, $servicio] = $this->crearContextoEmpresa('B10000002');
        $orden = app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($cliente->id, $servicio->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
        ]), $user);

        $nuevaFecha = now()->addDays(3)->setTime(9, 30)->toDateTimeString();
        app(OrdenTrabajoService::class)->actualizarOrden($orden, [
            'fecha_programada_inicio' => $nuevaFecha,
            'fecha_programada_fin' => now()->addDays(3)->setTime(11, 0)->toDateTimeString(),
        ], $user);

        $evento = CalendarioEvento::query()->where('orden_trabajo_id', $orden->id)->firstOrFail();
        $this->assertSame($nuevaFecha, $evento->inicio->format('Y-m-d H:i:s'));
    }

    public function test_una_orden_completada_marca_el_evento_como_completada(): void
    {
        [$user, $cliente, $servicio] = $this->crearContextoEmpresa('B10000003');
        $orden = app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($cliente->id, $servicio->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
        ]), $user);

        app(OrdenTrabajoService::class)->completarOrden($orden, $user);

        $this->assertDatabaseHas('calendario_eventos', [
            'orden_trabajo_id' => $orden->id,
            'estado_codigo' => 'completada',
        ]);
    }

    public function test_usuario_no_admin_no_puede_ver_eventos_de_otra_empresa(): void
    {
        [$userA, $clienteA, $servicioA] = $this->crearContextoEmpresa('B10000004');
        [$userB, $clienteB, $servicioB] = $this->crearContextoEmpresa('B10000005');

        app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($clienteA->id, $servicioA->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
        ]), $userA);
        app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($clienteB->id, $servicioB->id, [
            'fecha_programada_inicio' => now()->addDays(2)->toDateTimeString(),
        ]), $userB);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/empresa/calendario-eventos')
            ->assertOk();

        $this->assertCount(1, $response->json('data.data'));
        $this->assertSame($userA->empresa_id, $response->json('data.data.0.empresa_id'));
    }

    public function test_dashboard_devuelve_datos_filtrados_por_empresa(): void
    {
        [$userA, $clienteA, $servicioA] = $this->crearContextoEmpresa('B10000006');
        [$userB, $clienteB, $servicioB] = $this->crearContextoEmpresa('B10000007');

        $ordenA = app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($clienteA->id, $servicioA->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
        ]), $userA);
        app(OrdenTrabajoService::class)->crearOrden($this->datosOrden($clienteB->id, $servicioB->id, [
            'fecha_programada_inicio' => now()->addDay()->toDateTimeString(),
        ]), $userB);

        $factura = Factura::query()->create([
            'empresa_id' => $userA->empresa_id,
            'cliente_id' => $clienteA->id,
            'tipo_factura_id' => 1,
            'estado_factura_id' => 2,
            'serie' => 'A',
            'numero' => 'F-DASH-001',
            'fecha_emision' => now()->toDateString(),
            'fecha_operacion' => now()->toDateString(),
            'emisor_nif' => 'B10000006',
            'emisor_nombre_razon_social' => 'Empresa B10000006',
            'emisor_domicilio_fiscal' => 'N/D',
            'receptor_nif' => $clienteA->dni_cif,
            'receptor_nombre_razon_social' => $clienteA->nombre,
            'receptor_domicilio_fiscal' => 'N/D',
            'subtotal' => 100,
            'cuota_iva' => 21,
            'total' => 121,
        ]);
        RegistroFacturacion::query()->create([
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => 1,
            'modo_remision_facturacion_id' => 1,
            'empresa_id' => $userA->empresa_id,
            'emisor_nif' => 'B10000006',
            'emisor_nombre_razon_social' => 'Empresa B10000006',
            'serie' => 'A',
            'numero' => 'F-DASH-001',
            'fecha_expedicion' => now()->toDateString(),
            'tipo_factura_id' => 1,
            'cuota_total' => 21,
            'importe_total' => 121,
            'primer_registro_cadena' => true,
            'tipo_huella' => 'sha256',
            'hash_actual' => str_repeat('a', 64),
            'generado_at' => now(),
            'xml_contenido' => '<Registro />',
            'xml_version' => '1.0',
            'codigo_sistema_informatico' => 'TEST',
            'nombre_sistema' => 'MiNegocio',
            'version_sistema' => '1.0.0',
            'numero_instalacion' => 'EMP-'.$userA->empresa_id,
            'productor_nif' => 'B10000006',
            'productor_nombre' => 'MiNegocio',
            'estado_remision' => 'pendiente',
        ]);

        $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/empresa/dashboard/resumen')
            ->assertOk()
            ->assertJsonPath('data.clientes_activos', 1)
            ->assertJsonPath('data.ordenes_pendientes', 1)
            ->assertJsonPath('data.facturas_emitidas_mes', 1)
            ->assertJsonPath('data.total_facturado_mes', 121)
            ->assertJsonPath('data.registros_facturacion_pendientes', 1);

        $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/empresa/dashboard/proximas-ordenes')
            ->assertOk()
            ->assertJsonPath('data.0.id', $ordenA->id);

        $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/empresa/dashboard/calendario')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    private function crearContextoEmpresa(string $nif): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa '.$nif,
            'nif' => $nif,
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'calendario');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => 1,
            'nombre' => 'Cliente '.$nif,
            'dni_cif' => 'C'.$nif,
            'activo' => true,
        ]);
        $servicio = Servicio::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'SERV-'.$nif,
            'nombre' => 'Servicio '.$nif,
            'activo' => true,
        ]);

        return [$user, $cliente, $servicio];
    }

    private function datosOrden(int $clienteId, int $servicioId, array $overrides = []): array
    {
        return array_merge([
            'cliente_id' => $clienteId,
            'lineas' => [[
                'servicio_id' => $servicioId,
                'descripcion' => 'Servicio programado',
                'cantidad' => 1,
                'precio_unitario' => 100,
                'iva_porcentaje' => 21,
                'facturable' => true,
            ]],
        ], $overrides);
    }
}

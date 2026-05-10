<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoLinea;
use App\Models\RegistroFacturacion;
use App\Models\User;
use App\Services\FacturacionDesdeOrdenService;
use App\Services\ModuloService;
use App\Services\RegistroFacturacionCadenaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class FacturacionLegalFlowTest extends TestCase
{
    use RefreshDatabase;

    private const ESTADO_ORDEN_PENDIENTE = 'pendiente';
    private const ESTADO_ORDEN_COMPLETADA = 'completada';

    private const ESTADO_REMISION_PENDIENTE = 'pendiente';
    private const ESTADO_REMISION_ENVIADO_SIMULADO = 'enviado_simulado';

    private const EVENTO_ALTA_CREADO = 'REGISTRO_FACTURACION_ALTA_CREADO';
    private const EVENTO_ANULACION_CREADO = 'REGISTRO_FACTURACION_ANULACION_CREADO';
    private const EVENTO_ENVIADO_AEAT_SIMULADO = 'REGISTRO_FACTURACION_ENVIADO_AEAT_SIMULADO';

    private const TIPO_REGISTRO_ANULACION_ID = 2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_no_se_puede_generar_factura_si_orden_no_esta_completada(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_PENDIENTE, 'OT-001');

        $this->expectException(RuntimeException::class);

        $this->generarFacturaDesdeOrden($orden, $user);
    }

    public function test_orden_completada_genera_factura_y_registro_alta_y_eventos(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-002');

        $factura = $this->generarFacturaDesdeOrden($orden, $user);

        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
        ]);

        $this->assertDatabaseHas('registros_facturacion', [
            'factura_id' => $factura->id,
        ]);

        $this->assertDatabaseHas('registros_evento_facturacion', [
            'factura_id' => $factura->id,
            'codigo_evento' => self::EVENTO_ALTA_CREADO,
        ]);

        $this->assertSame(
            'facturada',
            $orden->fresh('estado')?->estado?->codigo
        );
    }

    public function test_no_duplica_facturacion_de_una_orden_ya_facturada(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-002-B');

        $this->generarFacturaDesdeOrden($orden, $user);

        $this->expectException(RuntimeException::class);

        $this->generarFacturaDesdeOrden($orden, $user);
    }

    public function test_dos_facturas_misma_empresa_quedan_encadenadas(): void
    {
        [$user, $orden1] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-003');
        [, $orden2] = $this->crearContextoOrden(
            self::ESTADO_ORDEN_COMPLETADA,
            'OT-004',
            (int) $user->empresa_id
        );

        $this->generarFacturaDesdeOrden($orden1, $user);
        $factura2 = $this->generarFacturaDesdeOrden($orden2, $user);

        $registros = RegistroFacturacion::query()
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $registros);
        $this->assertSame($registros[0]->hash_actual, $registros[1]->registro_anterior_hash_64);
        $this->assertSame($factura2->id, $registros[1]->factura_id);
    }

    public function test_anular_factura_genera_registro_anulacion_y_no_permita_doble_anulacion(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-005');

        $factura = $this->generarFacturaDesdeOrden($orden, $user);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/'.$factura->id.'/anular', [
                'motivo_anulacion' => 'Cliente solicita anulación',
            ])
            ->assertOk();

        $this->assertDatabaseHas('registros_facturacion', [
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => self::TIPO_REGISTRO_ANULACION_ID,
        ]);

        $this->assertDatabaseHas('registros_evento_facturacion', [
            'factura_id' => $factura->id,
            'codigo_evento' => self::EVENTO_ANULACION_CREADO,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/'.$factura->id.'/anular')
            ->assertStatus(500);
    }

    public function test_verifactu_simulado_envia_pendientes_y_registra_evento(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-010');

        $this->generarFacturaDesdeOrden($orden, $user);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verifactu/enviar-pendientes')
            ->assertOk();

        $this->assertGreaterThan(0, $response->json('data.enviados'));

        $this->assertDatabaseHas('registros_facturacion', [
            'estado_remision' => self::ESTADO_REMISION_ENVIADO_SIMULADO,
        ]);

        $this->assertDatabaseHas('registros_evento_facturacion', [
            'codigo_evento' => self::EVENTO_ENVIADO_AEAT_SIMULADO,
        ]);
    }

    public function test_verifactu_simulado_no_reenvia_registros_ya_enviados(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-010-B');

        $this->generarFacturaDesdeOrden($orden, $user);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verifactu/enviar-pendientes')
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verifactu/enviar-pendientes')
            ->assertOk()
            ->assertJsonPath('data.enviados', 0);
    }

    public function test_validacion_cadena_devuelve_valida(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-011');

        $this->generarFacturaDesdeOrden($orden, $user);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/registros-facturacion/validar-cadena')
            ->assertOk()
            ->assertJsonPath('data.valida', true);
    }

    public function test_validacion_cadena_detecta_enlace_roto(): void
    {
        [$user, $orden1] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-012');
        [, $orden2] = $this->crearContextoOrden(
            self::ESTADO_ORDEN_COMPLETADA,
            'OT-013',
            (int) $user->empresa_id
        );

        $this->generarFacturaDesdeOrden($orden1, $user);
        $this->generarFacturaDesdeOrden($orden2, $user);

        /** @var RegistroFacturacion $registro */
        $registro = RegistroFacturacion::query()
            ->where('empresa_id', $user->empresa_id)
            ->orderByDesc('id')
            ->firstOrFail();

        $registro->registro_anterior_hash_64 = str_repeat('0', 64);
        $registro->save();

        $resultado = app(RegistroFacturacionCadenaService::class)
            ->validarCadenaEmpresa((int) $user->empresa_id);

        $this->assertFalse($resultado['valida']);

        $this->assertTrue(
            collect($resultado['errores'])->contains(
                fn (array $error): bool => str_contains($error['motivo'], 'No enlaza')
            )
        );
    }

    public function test_validacion_cadena_detecta_primer_registro_mal_marcado(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-014');

        $this->generarFacturaDesdeOrden($orden, $user);

        /** @var RegistroFacturacion $registro */
        $registro = RegistroFacturacion::query()
            ->where('empresa_id', $user->empresa_id)
            ->firstOrFail();

        $registro->primer_registro_cadena = false;
        $registro->save();

        $resultado = app(RegistroFacturacionCadenaService::class)
            ->validarCadenaEmpresa((int) $user->empresa_id);

        $this->assertFalse($resultado['valida']);

        $this->assertTrue(
            collect($resultado['errores'])->contains(
                fn (array $error): bool => str_contains($error['motivo'], 'primer registro')
            )
        );
    }

    public function test_validacion_cadena_detecta_hash_manipulado(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-015');

        $this->generarFacturaDesdeOrden($orden, $user);

        /** @var RegistroFacturacion $registro */
        $registro = RegistroFacturacion::query()
            ->where('empresa_id', $user->empresa_id)
            ->firstOrFail();

        $registro->importe_total = 999.99;
        $registro->save();

        $resultado = app(RegistroFacturacionCadenaService::class)
            ->validarCadenaEmpresa((int) $user->empresa_id);

        $this->assertFalse($resultado['valida']);

        $this->assertTrue(
            collect($resultado['errores'])->contains(
                fn (array $error): bool => str_contains($error['motivo'], 'hash_actual')
            )
        );
    }

    public function test_factura_con_registro_no_permite_modificar_datos_fiscales_lineas_ni_impuestos(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-016');

        $factura = $this->generarFacturaDesdeOrden($orden, $user);

        try {
            $factura->receptor_nif = 'B00000000';
            $factura->save();

            $this->fail('Se permitió modificar una factura registrada.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('datos fiscales', $exception->getMessage());
        }

        try {
            $linea = $factura->lineas()->firstOrFail();
            $linea->descripcion = 'Manipulada';
            $linea->save();

            $this->fail('Se permitió modificar una línea registrada.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('líneas', $exception->getMessage());
        }

        $this->expectException(RuntimeException::class);

        $impuesto = $factura->impuestos()->firstOrFail();
        $impuesto->cuota = 999.99;
        $impuesto->save();
    }

    public function test_factura_rectificativa_referencia_original_genera_alta_y_entra_en_cadena(): void
    {
        [$user, $orden] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-017');

        $factura = $this->generarFacturaDesdeOrden($orden, $user);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/facturas/'.$factura->id.'/rectificar', [
                'motivo_rectificacion' => 'Corrección completa',
            ])
            ->assertCreated();

        $rectificativaId = $response->json('data.id');

        $this->assertDatabaseHas('facturas', [
            'id' => $rectificativaId,
            'factura_rectificada_id' => $factura->id,
        ]);

        $this->assertDatabaseHas('registros_facturacion', [
            'factura_id' => $rectificativaId,
        ]);

        $this->assertSame(
            2,
            RegistroFacturacion::query()
                ->where('empresa_id', $user->empresa_id)
                ->count()
        );

        $resultado = app(RegistroFacturacionCadenaService::class)
            ->validarCadenaEmpresa((int) $user->empresa_id);

        $this->assertTrue($resultado['valida']);
    }

    public function test_usuario_no_admin_no_puede_operar_sobre_empresa_ajena(): void
    {
        [$userEmpresaA, $ordenEmpresaA] = $this->crearContextoOrden(self::ESTADO_ORDEN_COMPLETADA, 'OT-018');

        $factura = $this->generarFacturaDesdeOrden($ordenEmpresaA, $userEmpresaA);

        /** @var Empresa $empresaB */
        $empresaB = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa B SL',
            'nif' => 'B87654321',
        ]);

        /** @var User $userEmpresaB */
        $userEmpresaB = User::factory()->create([
            'empresa_id' => $empresaB->id,
        ]);
        app(ModuloService::class)->activarModulo((int) $empresaB->id, 'facturacion');

        $this->actingAs($userEmpresaB, 'sanctum')
            ->postJson('/api/v1/facturas/'.$factura->id.'/anular')
            ->assertForbidden();

        $this->actingAs($userEmpresaB, 'sanctum')
            ->postJson('/api/v1/verifactu/enviar-pendientes')
            ->assertOk()
            ->assertJsonPath('data.enviados', 0);

        $this->assertDatabaseHas('registros_facturacion', [
            'factura_id' => $factura->id,
            'estado_remision' => self::ESTADO_REMISION_PENDIENTE,
        ]);
    }

    private function generarFacturaDesdeOrden(OrdenTrabajo $orden, User $user): Factura
    {
        return app(FacturacionDesdeOrdenService::class)->generarDesdeOrden(
            $this->ordenConRelaciones($orden),
            $user
        );
    }

    private function ordenConRelaciones(OrdenTrabajo $orden): OrdenTrabajo
    {
        /** @var OrdenTrabajo|null $ordenFresh */
        $ordenFresh = $orden->fresh([
            'estado',
            'lineas',
            'empresa',
            'cliente.localizacionPrincipal',
        ]);

        if (! $ordenFresh instanceof OrdenTrabajo) {
            $this->fail('No se pudo recargar la orden de trabajo.');
        }

        return $ordenFresh;
    }

    /**
     * @return array{0: User, 1: OrdenTrabajo}
     */
    private function crearContextoOrden(string $estadoCodigo, string $numeroOrden, ?int $empresaId = null): array
    {
        if ($empresaId === null) {
            /** @var Empresa $empresa */
            $empresa = Empresa::query()->create([
                'tipo_empresa_id' => 1,
                'nombre_fiscal' => 'ACME SL',
                'nif' => 'B12345678',
            ]);

            $empresaId = (int) $empresa->id;
        }

        /** @var User $user */
        $user = User::factory()->create([
            'empresa_id' => $empresaId,
        ]);
        app(ModuloService::class)->activarModulo((int) $empresaId, 'facturacion');

        $estadoId = $estadoCodigo === self::ESTADO_ORDEN_COMPLETADA ? 3 : 1;

        /** @var OrdenTrabajo $orden */
        $orden = OrdenTrabajo::query()->create([
            'empresa_id' => $empresaId,
            'cliente_id' => 1,
            'numero' => $numeroOrden,
            'estado_id' => $estadoId,
            'estado_codigo' => $estadoCodigo,
        ]);

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

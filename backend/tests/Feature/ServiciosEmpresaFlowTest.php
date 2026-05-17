<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use App\Models\TipoTarifaServicio;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiciosEmpresaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_empresa_puede_listar_sus_servicios(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000001');
        $this->crearServicio($empresa->id, 'LIMP');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios')
            ->assertOk()
            ->assertJsonPath('data.data.0.tipo_negocio', 'LIMP');
    }

    public function test_empresa_no_ve_servicios_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000002');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000003');
        $this->crearServicio($otraEmpresa->id, 'AJENA');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios')
            ->assertOk()
            ->assertJsonMissing(['tipo_negocio' => 'AJENA']);
    }

    public function test_empresa_crea_servicio_sin_enviar_empresa_id_y_backend_la_asigna(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000004');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/servicios', [
                'tipo_negocio' => 'Limpieza',
                'codigo' => 'CRIST',
                'nombre' => 'Limpieza de cristales',
                'unidad_servicio' => 'servicio',
            ])
            ->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id);
    }

    public function test_empresa_no_puede_editar_servicio_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000005');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000006');
        $servicioAjeno = $this->crearServicio($otraEmpresa->id, 'AJENA');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/servicios/{$servicioAjeno->id}", ['nombre' => 'No permitido'])
            ->assertNotFound();
    }

    public function test_empresa_puede_listar_tipos_globales_activos(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000007');
        $inactivo = TipoTarifaServicio::query()->where('codigo', 'nocturno')->firstOrFail();
        $inactivo->update(['activo' => false]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/tipos-tarifa-servicio')
            ->assertOk()
            ->assertJsonFragment(['codigo' => 'estandar'])
            ->assertJsonMissing(['codigo' => 'nocturno']);
    }

    public function test_empresa_crea_precio_para_su_servicio_y_tipo_global(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000010');
        $servicio = $this->crearServicio($empresa->id, 'Limpieza');
        $tipoTarifa = $this->tipoTarifa('estandar');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/servicios/{$servicio->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipoTarifa->id,
                'precio_base' => 30,
                'vigente_desde' => '2026-01-01',
            ])
            ->assertCreated()
            ->assertJsonPath('data.servicio_id', $servicio->id)
            ->assertJsonPath('data.tipo_tarifa_servicio_id', $tipoTarifa->id);
    }

    public function test_empresa_no_puede_crear_precio_para_servicio_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000011');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000012');
        $servicioAjeno = $this->crearServicio($otraEmpresa->id, 'Ajena');
        $tipoTarifa = $this->tipoTarifa('estandar');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/servicios/{$servicioAjeno->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipoTarifa->id,
                'precio_base' => 30,
            ])
            ->assertStatus(422);
    }

    public function test_empresa_no_puede_usar_tipo_tarifa_inactivo(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000013');
        $servicio = $this->crearServicio($empresa->id, 'Limpieza');
        $tipoTarifa = $this->tipoTarifa('nocturno');
        $tipoTarifa->update(['activo' => false]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/servicios/{$servicio->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipoTarifa->id,
                'precio_base' => 30,
            ])
            ->assertStatus(422);
    }

    public function test_empresa_no_puede_editar_precio_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000014');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000015');
        $servicioAjeno = $this->crearServicio($otraEmpresa->id, 'Ajena');
        $precioAjeno = $this->crearPrecio($servicioAjeno->id, $this->tipoTarifa('estandar')->id);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/servicio-precios/{$precioAjeno->id}", ['precio_base' => 99])
            ->assertNotFound();
    }

    public function test_dos_empresas_usan_mismo_tipo_global_con_precios_distintos(): void
    {
        [$userA, $empresaA] = $this->crearUsuarioEmpresa('B32000016');
        [$userB, $empresaB] = $this->crearUsuarioEmpresa('B32000017');
        $tipo = $this->tipoTarifa('estandar');
        $servicioA = $this->crearServicio($empresaA->id, 'Limpieza', 'SERVA');
        $servicioB = $this->crearServicio($empresaB->id, 'Limpieza', 'SERVB');

        $this->actingAs($userA, 'sanctum')->postJson("/api/v1/servicios/{$servicioA->id}/precios", [
            'tipo_tarifa_servicio_id' => $tipo->id,
            'precio_base' => 30,
        ])->assertCreated();

        $this->actingAs($userB, 'sanctum')->postJson("/api/v1/servicios/{$servicioB->id}/precios", [
            'tipo_tarifa_servicio_id' => $tipo->id,
            'precio_base' => 50,
        ])->assertCreated();

        $this->assertDatabaseHas('servicio_precios', ['servicio_id' => $servicioA->id, 'tipo_tarifa_servicio_id' => $tipo->id, 'precio_base' => 30]);
        $this->assertDatabaseHas('servicio_precios', ['servicio_id' => $servicioB->id, 'tipo_tarifa_servicio_id' => $tipo->id, 'precio_base' => 50]);
    }

    public function test_nuevo_precio_cierra_precio_vigente_anterior(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000018');
        $servicio = $this->crearServicio($empresa->id, 'Limpieza');
        $tipo = $this->tipoTarifa('estandar');
        $precioAnterior = $this->crearPrecio($servicio->id, $tipo->id, 30, '2026-01-01 00:00:00');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/servicios/{$servicio->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipo->id,
                'precio_base' => 40,
                'vigente_desde' => '2026-02-01',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('servicio_precios', [
            'id' => $precioAnterior->id,
            'vigente_hasta' => '2026-02-01',
        ]);
    }

    public function test_categorias_logicas_funciona_sin_servicios(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000019');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios-categorias-logicas')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_categorias_logicas_solo_devuelven_tipo_negocio_de_la_empresa(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000020');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000021');
        $this->crearServicio($empresa->id, 'Limpieza');
        $this->crearServicio($otraEmpresa->id, 'Oculta');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios-categorias-logicas')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Limpieza'])
            ->assertJsonMissing(['nombre' => 'Oculta']);
    }

    public function test_renombrar_fusionar_y_vaciar_categoria_solo_afecta_empresa_autenticada(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000022');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000023');
        $propio = $this->crearServicio($empresa->id, 'Cristales');
        $otroPropio = $this->crearServicio($empresa->id, 'Limpieza', 'SERV2');
        $ajeno = $this->crearServicio($otraEmpresa->id, 'Cristales');

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/servicios-categorias-logicas/renombrar', [
                'actual' => 'Cristales',
                'nuevo' => 'Cristales premium',
            ])
            ->assertOk();

        $this->assertDatabaseHas('servicios', ['id' => $propio->id, 'tipo_negocio' => 'Cristales premium']);
        $this->assertDatabaseHas('servicios', ['id' => $ajeno->id, 'tipo_negocio' => 'Cristales']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/servicios-categorias-logicas/fusionar', [
                'origen' => 'Cristales premium',
                'destino' => 'Limpieza',
            ])
            ->assertOk();

        $this->assertDatabaseHas('servicios', ['id' => $propio->id, 'tipo_negocio' => 'Limpieza']);
        $this->assertDatabaseHas('servicios', ['id' => $otroPropio->id, 'tipo_negocio' => 'Limpieza']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/servicios-categorias-logicas/vaciar', ['categoria' => 'Limpieza'])
            ->assertOk();

        $this->assertDatabaseHas('servicios', ['id' => $propio->id, 'tipo_negocio' => null]);
        $this->assertDatabaseHas('servicios', ['id' => $otroPropio->id, 'tipo_negocio' => null]);
        $this->assertDatabaseHas('servicios', ['id' => $ajeno->id, 'tipo_negocio' => 'Cristales']);
    }

    public function test_rutas_requieren_modulo_servicios_activo(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000024', false);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios')
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Empresa}
     */
    private function crearUsuarioEmpresa(string $nif, bool $activarModulo = true): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => (int) DB::table('tipos_empresa')->value('id'),
            'nombre_fiscal' => 'Empresa '.$nif,
            'nombre_comercial' => 'Comercial '.$nif,
            'nif' => $nif,
            'correo' => strtolower($nif).'@example.com',
            'telefono' => '600000000',
            'activa' => true,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id' => (int) DB::table('roles')->where('nombre', 'titular')->value('id'),
            'activo' => true,
        ]);

        if ($activarModulo) {
            app(ModuloService::class)->activarModulo((int) $empresa->id, 'servicios');
        }

        return [$user, $empresa];
    }

    private function crearServicio(int $empresaId, string $tipoNegocio, string $codigo = 'SERV'): Servicio
    {
        return Servicio::query()->create([
            'empresa_id' => $empresaId,
            'tipo_negocio' => $tipoNegocio,
            'codigo' => $codigo,
            'nombre' => 'Servicio '.$codigo,
            'unidad_servicio' => 'servicio',
            'activo' => true,
        ]);
    }

    private function tipoTarifa(string $codigo): TipoTarifaServicio
    {
        return TipoTarifaServicio::query()->where('codigo', $codigo)->firstOrFail();
    }

    private function crearPrecio(int $servicioId, int $tipoTarifaId, int $precio = 30, string $vigenteDesde = '2026-01-01 00:00:00'): ServicioPrecio
    {
        return ServicioPrecio::query()->create([
            'servicio_id' => $servicioId,
            'tipo_tarifa_servicio_id' => $tipoTarifaId,
            'precio_base' => $precio,
            'iva_porcentaje' => 21,
            'moneda' => 'EUR',
            'vigente_desde' => $vigenteDesde,
        ]);
    }
}

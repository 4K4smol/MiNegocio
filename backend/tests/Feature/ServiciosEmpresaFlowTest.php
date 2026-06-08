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
            ->getJson('/api/v1/empresa/servicios')
            ->assertOk()
            ->assertJsonPath('data.data.0.tipo_negocio', 'LIMP');
    }

    public function test_empresa_puede_consultar_un_servicio_para_editar(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000008');
        $servicio = $this->crearServicio($empresa->id, 'LIMP');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/empresa/servicios/{$servicio->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $servicio->id)
            ->assertJsonPath('data.tipo_negocio', 'LIMP');
    }

    public function test_empresa_no_ve_servicios_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000002');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B32000003');
        $this->crearServicio($otraEmpresa->id, 'AJENA');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/empresa/servicios')
            ->assertOk()
            ->assertJsonMissing(['tipo_negocio' => 'AJENA']);
    }

    public function test_empresa_crea_servicio_sin_enviar_empresa_id_y_backend_la_asigna(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000004');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/servicios', [
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
            ->putJson("/api/v1/empresa/servicios/{$servicioAjeno->id}", ['nombre' => 'No permitido'])
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
            ->postJson("/api/v1/empresa/servicios/{$servicio->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipoTarifa->id,
                'precio_base' => 30,
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
            ->postJson("/api/v1/empresa/servicios/{$servicioAjeno->id}/precios", [
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
            ->postJson("/api/v1/empresa/servicios/{$servicio->id}/precios", [
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
            ->putJson("/api/v1/empresa/servicio-precios/{$precioAjeno->id}", ['precio_base' => 99])
            ->assertNotFound();
    }

    public function test_dos_empresas_usan_mismo_tipo_global_con_precios_distintos(): void
    {
        [$userA, $empresaA] = $this->crearUsuarioEmpresa('B32000016');
        [$userB, $empresaB] = $this->crearUsuarioEmpresa('B32000017');
        $tipo = $this->tipoTarifa('estandar');
        $servicioA = $this->crearServicio($empresaA->id, 'Limpieza', 'SERVA');
        $servicioB = $this->crearServicio($empresaB->id, 'Limpieza', 'SERVB');

        $this->actingAs($userA, 'sanctum')->postJson("/api/v1/empresa/servicios/{$servicioA->id}/precios", [
            'tipo_tarifa_servicio_id' => $tipo->id,
            'precio_base' => 30,
        ])->assertCreated();

        $this->actingAs($userB, 'sanctum')->postJson("/api/v1/empresa/servicios/{$servicioB->id}/precios", [
            'tipo_tarifa_servicio_id' => $tipo->id,
            'precio_base' => 50,
        ])->assertCreated();

        $this->assertDatabaseHas('servicio_precios', ['servicio_id' => $servicioA->id, 'tipo_tarifa_servicio_id' => $tipo->id, 'precio_base' => 30]);
        $this->assertDatabaseHas('servicio_precios', ['servicio_id' => $servicioB->id, 'tipo_tarifa_servicio_id' => $tipo->id, 'precio_base' => 50]);
    }

    public function test_no_permite_duplicar_precio_para_servicio_y_tipo_tarifa(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000018');
        $servicio = $this->crearServicio($empresa->id, 'Limpieza');
        $tipo = $this->tipoTarifa('estandar');
        $this->crearPrecio($servicio->id, $tipo->id, 30);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/empresa/servicios/{$servicio->id}/precios", [
                'tipo_tarifa_servicio_id' => $tipo->id,
                'precio_base' => 40,
            ])
            ->assertStatus(422);
    }

    public function test_empresa_actualiza_precio_existente_para_servicio_y_tipo_tarifa(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B32000026');
        $servicio = $this->crearServicio($empresa->id, 'Limpieza');
        $tipo = $this->tipoTarifa('estandar');
        $precio = $this->crearPrecio($servicio->id, $tipo->id, 30);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/empresa/servicio-precios/{$precio->id}", [
                'precio_base' => 40,
            ])
            ->assertOk()
            ->assertJsonPath('data.precio_base', '40.00');
    }

    public function test_rutas_requieren_modulo_servicios_activo(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000024', false);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/empresa/servicios')
            ->assertForbidden();
    }

    public function test_rutas_de_categorias_logicas_no_existen(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B32000025');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/servicios-categorias-logicas')
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/servicios-categorias-logicas/renombrar', [
                'actual' => 'Limpieza',
                'nuevo' => 'Mantenimiento',
            ])
            ->assertNotFound();
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

    private function crearPrecio(int $servicioId, int $tipoTarifaId, int $precio = 30): ServicioPrecio
    {
        return ServicioPrecio::query()->create([
            'servicio_id' => $servicioId,
            'tipo_tarifa_servicio_id' => $tipoTarifaId,
            'precio_base' => $precio,
            'iva_porcentaje' => 21,
            'moneda' => 'EUR',
        ]);
    }
}

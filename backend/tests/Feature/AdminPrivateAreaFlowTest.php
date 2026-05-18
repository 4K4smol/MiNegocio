<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPrivateAreaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_puede_ver_dashboard(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['metricas', 'solicitudes_recientes', 'eventos_recientes']]);
    }

    public function test_usuario_no_admin_no_puede_ver_dashboard(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_lista_activa_desactiva_y_cambia_rol_usuario(): void
    {
        $admin = $this->crearAdmin();
        $usuario = $this->crearUsuario();
        $adminRoleId = $this->roleId('admin');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/usuarios')
            ->assertOk()
            ->assertJsonStructure(['data' => ['data']]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$usuario->id}/activar")
            ->assertOk();

        $this->assertTrue($usuario->fresh()->activo);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$usuario->id}/rol", ['role_id' => $adminRoleId])
            ->assertOk();

        $this->assertSame($adminRoleId, (int) $usuario->fresh()->role_id);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$usuario->id}/desactivar")
            ->assertOk();

        $this->assertFalse($usuario->fresh()->activo);
        $this->assertDatabaseHas('admin_verificacion_eventos', ['accion' => 'desactivar_usuario']);
    }

    public function test_admin_no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$admin->id}/desactivar")
            ->assertStatus(422);
    }

    public function test_no_se_puede_dejar_sin_administradores_activos(): void
    {
        $actor = $this->crearAdmin(['activo' => false]);
        $ultimoAdmin = $this->crearAdmin();

        User::query()
            ->where('role_id', $this->roleId('admin'))
            ->whereKeyNot($ultimoAdmin->id)
            ->update(['activo' => false]);

        $this->actingAs($actor, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$ultimoAdmin->id}/desactivar")
            ->assertStatus(422);
    }

    public function test_admin_lista_empresas_y_gestiona_modulos(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal' => 'Empresa Modulos',
            'nif' => 'B12345000',
            'activa' => false,
        ]);
        $modulo = Modulo::query()->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/empresas')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/empresas/{$empresa->id}/activar")
            ->assertOk();

        $this->assertTrue($empresa->fresh()->activa);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/empresas/{$empresa->id}/modulos/{$modulo->codigo}/activar")
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admin/empresas/{$empresa->id}/modulos")
            ->assertOk()
            ->assertJsonPath('data.empresa_id', $empresa->id);
    }

    public function test_admin_lista_auditoria_y_catalogos(): void
    {
        $admin = $this->crearAdmin();

        DB::table('admin_verificacion_eventos')->insert([
            'user_admin_id' => $admin->id,
            'accion' => 'evento_test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/auditoria')
            ->assertOk()
            ->assertJsonStructure(['data' => ['data']]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/catalogos')
            ->assertOk()
            ->assertJsonStructure(['data' => ['roles', 'estados_verificacion', 'tipos_empresa']]);
    }

    public function test_usuario_no_admin_no_puede_acceder_a_catalogos_admin(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum')
            ->getJson('/api/v1/admin/catalogos')
            ->assertForbidden();
    }

    private function crearAdmin(array $extra = []): User
    {
        return User::factory()->create($extra + [
            'empresa_id' => null,
            'role_id' => $this->roleId('admin'),
            'activo' => true,
        ]);
    }

    private function crearUsuario(array $extra = []): User
    {
        return User::factory()->create($extra + [
            'empresa_id' => null,
            'role_id' => $this->roleId('titular'),
            'activo' => false,
        ]);
    }

    private function roleId(string $nombre): int
    {
        return (int) DB::table('roles')->where('nombre', $nombre)->value('id');
    }

    private function tipoEmpresaId(string $nombre): int
    {
        return (int) DB::table('tipos_empresa')->where('nombre', $nombre)->value('id');
    }
}

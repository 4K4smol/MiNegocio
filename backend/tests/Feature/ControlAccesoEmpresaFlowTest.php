<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ControlAccesoEmpresaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_es_bloqueado_en_dashboard_de_empresa(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/dashboard/resumen')
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_admin_es_bloqueado_en_clientes(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_admin_es_bloqueado_en_ordenes_de_trabajo(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/ordenes-trabajo')
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_usuario_de_empresa_puede_acceder_al_dashboard(): void
    {
        $usuario = $this->crearUsuarioConEmpresa('B40000001');

        $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/v1/dashboard/resumen')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_usuario_sin_empresa_id_es_bloqueado_en_rutas_crm(): void
    {
        $usuario = $this->crearUsuarioSinEmpresa();

        $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/v1/dashboard/resumen')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'El usuario no tiene empresa asociada.');
    }

    public function test_bloqueo_admin_devuelve_estructura_json_coherente(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/dashboard/resumen')
            ->assertForbidden()
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }

    public function test_bloqueo_sin_empresa_devuelve_estructura_json_coherente(): void
    {
        $this->actingAs($this->crearUsuarioSinEmpresa(), 'sanctum')
            ->getJson('/api/v1/dashboard/resumen')
            ->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }

    public function test_admin_sigue_accediendo_a_sus_propias_rutas(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk();
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('admin'),
            'activo'     => true,
        ]);
    }

    private function crearUsuarioConEmpresa(string $nif): User
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal'   => 'Empresa ' . $nif,
            'nif'             => $nif,
            'activa'          => true,
        ]);

        return User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id'    => $this->roleId('titular'),
            'activo'     => true,
        ]);
    }

    private function crearUsuarioSinEmpresa(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('titular'),
            'activo'     => true,
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

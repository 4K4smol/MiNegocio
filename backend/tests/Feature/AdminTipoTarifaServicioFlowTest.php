<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTipoTarifaServicioFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_gestiona_tipos_de_tarifa_servicio(): void
    {
        $admin = $this->crearUsuario('admin');

        $id = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/tipos-tarifa-servicio', [
                'codigo' => 'premium',
                'nombre' => 'Premium',
                'descripcion' => 'Tarifa premium',
                'orden' => 20,
                'activo' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.codigo', 'premium')
            ->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/tipos-tarifa-servicio')
            ->assertOk()
            ->assertJsonFragment(['codigo' => 'premium']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/tipos-tarifa-servicio/{$id}", ['nombre' => 'Premium plus'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Premium plus');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/tipos-tarifa-servicio/{$id}/desactivar")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/tipos-tarifa-servicio/{$id}/activar")
            ->assertOk()
            ->assertJsonPath('data.activo', true);
    }

    public function test_usuario_empresa_no_puede_modificar_tipos_globales(): void
    {
        $empresaUser = $this->crearUsuario('titular');

        $this->actingAs($empresaUser, 'sanctum')
            ->postJson('/api/v1/admin/tipos-tarifa-servicio', [
                'codigo' => 'bloqueada',
                'nombre' => 'Bloqueada',
            ])
            ->assertForbidden();
    }

    private function crearUsuario(string $rol): User
    {
        return User::factory()->create([
            'empresa_id' => $rol === 'admin' ? null : DB::table('empresas')->value('id'),
            'role_id' => (int) DB::table('roles')->where('nombre', $rol)->value('id'),
            'activo' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EstadoFactura;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EstadoFacturaCatalogoFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_usuario_autenticado_puede_listar_estados_factura(): void
    {
        $this->actingAs($this->crearUsuarioAutenticado(), 'sanctum')
            ->getJson('/api/v1/estados-factura')
            ->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonPath('success', true);
    }

    public function test_usuario_autenticado_puede_ver_un_estado_factura(): void
    {
        $estado = EstadoFactura::query()->firstOrFail();

        $this->actingAs($this->crearUsuarioAutenticado(), 'sanctum')
            ->getJson("/api/v1/estados-factura/{$estado->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $estado->id);
    }

    public function test_usuario_no_admin_no_puede_crear_estado_factura(): void
    {
        $this->actingAs($this->crearUsuarioAutenticado(), 'sanctum')
            ->postJson('/api/v1/estados-factura', [
                'codigo' => 'TEST',
                'nombre' => 'Estado Test',
            ])
            ->assertForbidden();
    }

    public function test_usuario_no_admin_no_puede_editar_estado_factura(): void
    {
        $estado = EstadoFactura::query()->firstOrFail();

        $this->actingAs($this->crearUsuarioAutenticado(), 'sanctum')
            ->putJson("/api/v1/estados-factura/{$estado->id}", [
                'codigo' => 'EDITADO',
                'nombre' => 'Nombre Editado',
            ])
            ->assertForbidden();
    }

    public function test_usuario_no_admin_no_puede_eliminar_estado_factura(): void
    {
        $estado = EstadoFactura::query()->firstOrFail();

        $this->actingAs($this->crearUsuarioAutenticado(), 'sanctum')
            ->deleteJson("/api/v1/estados-factura/{$estado->id}")
            ->assertForbidden();
    }

    public function test_admin_puede_crear_estado_factura(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson('/api/v1/estados-factura', [
                'codigo' => 'BORRADOR',
                'nombre' => 'Borrador',
                'activo' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.codigo', 'BORRADOR');

        $this->assertDatabaseHas('estados_factura', ['codigo' => 'BORRADOR']);
    }

    public function test_admin_puede_editar_estado_factura(): void
    {
        $estado = EstadoFactura::query()->create([
            'codigo' => 'EDITABLE',
            'nombre' => 'Editable',
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->putJson("/api/v1/estados-factura/{$estado->id}", [
                'codigo' => 'EDITABLE',
                'nombre' => 'Nombre Actualizado',
            ])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Nombre Actualizado');

        $this->assertDatabaseHas('estados_factura', [
            'id'     => $estado->id,
            'nombre' => 'Nombre Actualizado',
        ]);
    }

    public function test_admin_puede_eliminar_estado_factura(): void
    {
        $estado = EstadoFactura::query()->create([
            'codigo' => 'BORRABLE',
            'nombre' => 'Borrable',
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->deleteJson("/api/v1/estados-factura/{$estado->id}")
            ->assertOk();

        $this->assertDatabaseMissing('estados_factura', ['id' => $estado->id]);
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('admin'),
            'activo'     => true,
        ]);
    }

    private function crearUsuarioAutenticado(): User
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
}

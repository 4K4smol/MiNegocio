<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_no_autenticado_recibe_401(): void
    {
        $this->getJson('/api/v1/admin/solicitudes')->assertUnauthorized();
    }

    public function test_usuario_no_admin_recibe_403(): void
    {
        [$user] = $this->crearSolicitud('B11111111', 'sociedad');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/solicitudes')
            ->assertForbidden();
    }

    public function test_admin_puede_listar_y_ver_solicitud(): void
    {
        [, , $solicitud] = $this->crearSolicitud('B22222222', 'sociedad');
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/solicitudes')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admin/solicitudes/{$solicitud->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $solicitud->id);
    }

    public function test_admin_aprueba_identidad_y_rechaza_con_motivo(): void
    {
        [, , $solicitud] = $this->crearSolicitud('B33333333', 'sociedad');
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-identidad")
            ->assertOk()
            ->assertJsonPath('data.estado_identidad', 'aprobada');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes/{$solicitud->id}/rechazar-empresa", [
                'motivo_rechazo' => 'Documentacion fiscal ilegible.',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_empresa', 'rechazada');

        $this->assertDatabaseHas('admin_verificacion_eventos', [
            'solicitud_verificacion_id' => $solicitud->id,
            'accion' => 'rechazar_empresa',
            'motivo' => 'Documentacion fiscal ilegible.',
        ]);
    }

    public function test_no_aprueba_total_si_faltan_fases_obligatorias(): void
    {
        [, , $solicitud] = $this->crearSolicitud('B44444444', 'sociedad');
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-total")
            ->assertStatus(422);
    }

    public function test_autonomo_no_requiere_representacion_para_aprobacion_total(): void
    {
        [$user, $empresa, $solicitud] = $this->crearSolicitud('A55555555', 'autonomo');
        $admin = $this->crearAdmin();

        $solicitud->update([
            'estado_identidad' => 'aprobada',
            'estado_empresa' => 'aprobada',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-total")
            ->assertOk()
            ->assertJsonPath('data.estado_verificacion', 'aprobada');

        $this->assertTrue($user->fresh()->activo);
        $this->assertTrue($empresa->fresh()->activa);
    }

    public function test_sociedad_requiere_representacion_para_aprobacion_total(): void
    {
        [, , $solicitud] = $this->crearSolicitud('B66666666', 'sociedad');
        $admin = $this->crearAdmin();

        $solicitud->update([
            'estado_identidad' => 'aprobada',
            'estado_empresa' => 'aprobada',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-total")
            ->assertStatus(422);
    }

    /**
     * @return array{0: User, 1: Empresa, 2: SolicitudVerificacion}
     */
    private function crearSolicitud(string $nif, string $tipoEmpresa): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId($tipoEmpresa),
            'nombre_fiscal' => 'Empresa '.$nif,
            'nombre_comercial' => 'Comercial '.$nif,
            'nif' => $nif,
            'activa' => false,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id' => $this->roleId('titular'),
            'activo' => false,
        ]);

        $solicitud = SolicitudVerificacion::query()->create([
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'estado_verificacion_id' => $this->estadoId('pendiente'),
            'estado_identidad' => 'pendiente',
            'estado_empresa' => 'pendiente',
            'estado_representacion' => $tipoEmpresa === 'autonomo' ? null : 'pendiente',
        ]);

        return [$user, $empresa, $solicitud];
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id' => $this->roleId('admin'),
            'activo' => true,
        ]);
    }

    private function roleId(string $nombre): int
    {
        return (int) DB::table('roles')->where('nombre', $nombre)->value('id');
    }

    private function estadoId(string $nombre): int
    {
        return (int) DB::table('estados_verificacion')->where('nombre', $nombre)->value('id');
    }

    private function tipoEmpresaId(string $nombre): int
    {
        return (int) DB::table('tipos_empresa')->where('nombre', $nombre)->value('id');
    }
}

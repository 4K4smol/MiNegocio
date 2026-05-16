<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificacionUsuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VerificacionUsuarioFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_usuario_puede_ver_su_propia_verificacion(): void
    {
        $usuario = $this->crearUsuario();
        $this->crearVerificacion($usuario->id);

        $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/v1/verificaciones-usuario/me')
            ->assertOk()
            ->assertJsonPath('data.user_id', $usuario->id);
    }

    public function test_usuario_sin_verificacion_recibe_404(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum')
            ->getJson('/api/v1/verificaciones-usuario/me')
            ->assertNotFound();
    }

    public function test_usuario_puede_crear_su_verificacion_y_queda_en_estado_pendiente(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/verificaciones-usuario', [
                'tipo_documento_identidad_id' => $this->tipoDocumentoId(),
                'numero_documento'            => 'X9999999Z',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $usuario->id)
            ->assertJsonPath('data.estado_verificacion_id', $this->estadoVerificacionId('pendiente'));

        $this->assertDatabaseHas('verificaciones_usuario', [
            'user_id'          => $usuario->id,
            'numero_documento' => 'X9999999Z',
        ]);
    }

    public function test_usuario_no_puede_crear_verificacion_dos_veces(): void
    {
        $usuario = $this->crearUsuario();
        $this->crearVerificacion($usuario->id);

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/verificaciones-usuario', [
                'tipo_documento_identidad_id' => $this->tipoDocumentoId(),
                'numero_documento'            => 'X8888888Y',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.user_id.0', 'Ya existe una verificación de identidad para este usuario.');
    }

    public function test_user_id_enviado_en_el_request_es_ignorado(): void
    {
        $usuario = $this->crearUsuario();
        $otroUsuario = $this->crearUsuario();

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/verificaciones-usuario', [
                'tipo_documento_identidad_id' => $this->tipoDocumentoId(),
                'numero_documento'            => 'X7777777X',
                'user_id'                     => $otroUsuario->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $usuario->id);
    }

    public function test_usuario_no_puede_listar_todas_las_verificaciones(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum')
            ->getJson('/api/v1/admin/verificaciones-usuario')
            ->assertForbidden();
    }

    public function test_admin_puede_listar_todas_las_verificaciones(): void
    {
        $usuario = $this->crearUsuario();
        $this->crearVerificacion($usuario->id);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/verificaciones-usuario')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_admin_puede_ver_una_verificacion_concreta(): void
    {
        $usuario = $this->crearUsuario();
        $verificacion = $this->crearVerificacion($usuario->id);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson("/api/v1/admin/verificaciones-usuario/{$verificacion->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $verificacion->id);
    }

    public function test_admin_actualiza_verificacion_y_revisado_por_se_asigna_automaticamente(): void
    {
        $usuario = $this->crearUsuario();
        $verificacion = $this->crearVerificacion($usuario->id);
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/verificaciones-usuario/{$verificacion->id}", [
                'notas_revision' => 'Documentación revisada.',
            ])
            ->assertOk()
            ->assertJsonPath('data.revisado_por', $admin->id);

        $this->assertDatabaseHas('verificaciones_usuario', [
            'id'          => $verificacion->id,
            'revisado_por' => $admin->id,
        ]);
    }

    public function test_admin_aprueba_verificacion_y_fecha_verificacion_se_asigna_automaticamente(): void
    {
        $usuario = $this->crearUsuario();
        $verificacion = $this->crearVerificacion($usuario->id);
        $admin = $this->crearAdmin();

        $aprobadoId = $this->estadoVerificacionId('aprobado');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/verificaciones-usuario/{$verificacion->id}", [
                'estado_verificacion_id' => $aprobadoId,
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_verificacion_id', $aprobadoId);

        $this->assertDatabaseHas('verificaciones_usuario', [
            'id'                      => $verificacion->id,
            'estado_verificacion_id'  => $aprobadoId,
        ]);

        $fechaVerificacion = VerificacionUsuario::query()->find($verificacion->id)?->fecha_verificacion;
        $this->assertNotNull($fechaVerificacion, 'fecha_verificacion debe asignarse automáticamente al aprobar.');
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('admin'),
            'activo'     => true,
        ]);
    }

    private function crearUsuario(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('titular'),
            'activo'     => true,
        ]);
    }

    private function crearVerificacion(int $userId): VerificacionUsuario
    {
        return VerificacionUsuario::query()->create([
            'user_id'                     => $userId,
            'estado_verificacion_id'      => $this->estadoVerificacionId('pendiente'),
            'tipo_documento_identidad_id' => $this->tipoDocumentoId(),
            'numero_documento'            => 'DOC' . $userId,
        ]);
    }

    private function roleId(string $nombre): int
    {
        return (int) DB::table('roles')->where('nombre', $nombre)->value('id');
    }

    private function estadoVerificacionId(string $nombre): int
    {
        return (int) DB::table('estados_verificacion')->where('nombre', $nombre)->value('id');
    }

    private function tipoDocumentoId(): int
    {
        return (int) DB::table('tipos_documento_identidad')->value('id');
    }
}

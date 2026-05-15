<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminModernFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_moderno_ve_dashboard_y_no_admin_recibe_403(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk();

        $this->actingAs($this->crearUsuario(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_lista_y_ve_detalle_de_solicitud_moderna_con_empresa_id(): void
    {
        [, $empresa] = $this->crearSolicitud('B77000001');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/solicitudes-verificacion')
            ->assertOk();

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}")
            ->assertOk()
            ->assertJsonPath('data.empresa.id', $empresa->id);
    }

    public function test_admin_aprueba_rechaza_y_solicita_subsanacion_con_empresa_id(): void
    {
        [$userAprobar, $empresaAprobar, $solicitudAprobar] = $this->crearSolicitud('B77000002');
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaAprobar->id}/fases/identidad/aprobar")
            ->assertOk();
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaAprobar->id}/fases/empresa/aprobar")
            ->assertOk();
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaAprobar->id}/fases/representacion/aprobar")
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaAprobar->id}/aprobar", ['observaciones' => 'Correcto.'])
            ->assertOk();

        $this->assertTrue($empresaAprobar->fresh()->activa);
        $this->assertTrue($userAprobar->fresh()->activo);
        $this->assertSame('aprobada', $solicitudAprobar->fresh()->estadoVerificacion->nombre);
        $this->assertDatabaseHas('admin_verificacion_eventos', ['accion' => 'aprobar_solicitud']);

        [, $empresaRechazar] = $this->crearSolicitud('B77000003');
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaRechazar->id}/rechazar", ['motivo' => 'No acredita representacion.'])
            ->assertOk();

        [, $empresaSubsanar] = $this->crearSolicitud('B77000004');
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresaSubsanar->id}/solicitar-subsanacion", [
                'motivo' => 'Falta documento.',
                'documentos_requeridos' => ['identidad'],
            ])
            ->assertOk();
    }

    public function test_rechazo_sin_motivo_devuelve_422(): void
    {
        [, $empresa] = $this->crearSolicitud('B77000005');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/rechazar")
            ->assertStatus(422);
    }

    public function test_admin_lista_usuarios_auditoria_y_catalogos_y_no_puede_desactivarse(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/usuarios')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/usuarios/{$admin->id}/desactivar")
            ->assertStatus(422);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/auditoria')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/catalogos')
            ->assertOk();
    }

    public function test_preview_documento_devuelve_inline(): void
    {
        Storage::fake('verificaciones');
        [, , $solicitud] = $this->crearSolicitud('B77000006');
        Storage::disk('verificaciones')->put('docs/test.pdf', '%PDF-1.4');

        $documento = DocumentoVerificacion::query()->create([
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'identidad',
            'archivo' => 'docs/test.pdf',
            'nombre_original' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'tamano' => 8,
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->get("/api/v1/admin/documentos-verificacion/{$documento->id}/preview")
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="test.pdf"');
    }

    /**
     * @return array{0: User, 1: Empresa, 2: SolicitudVerificacion}
     */
    private function crearSolicitud(string $nif): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal' => 'Empresa '.$nif,
            'nif' => $nif,
            'activa' => false,
        ]);

        $user = $this->crearUsuario(['empresa_id' => $empresa->id]);

        $solicitud = SolicitudVerificacion::query()->create([
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'estado_verificacion_id' => $this->estadoId('pendiente'),
            'estado_identidad' => 'pendiente',
            'estado_empresa' => 'pendiente',
            'estado_representacion' => 'pendiente',
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

    private function estadoId(string $nombre): int
    {
        return (int) DB::table('estados_verificacion')->where('nombre', $nombre)->value('id');
    }

    private function tipoEmpresaId(string $nombre): int
    {
        return (int) DB::table('tipos_empresa')->where('nombre', $nombre)->value('id');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use App\Models\VerificacionEmpresa;
use App\Models\VerificacionUsuario;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSolicitudVerificacionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_puede_listar_solicitudes_pendientes(): void
    {
        $this->crearSolicitud('B10000001', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/admin/solicitudes-verificacion?estado=pendiente')
            ->assertOk()
            ->assertJsonPath('data.data.0.estado_verificacion', 'pendiente')
            ->assertJsonStructure(['data' => ['data' => [['empresa', 'responsable', 'acciones_disponibles']]]]);
    }

    public function test_usuario_no_admin_no_puede_listar_solicitudes(): void
    {
        [$user] = $this->crearSolicitud('B10000002', 'sociedad');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/solicitudes-verificacion')
            ->assertForbidden();
    }

    public function test_admin_puede_ver_detalle_de_solicitud(): void
    {
        [, $empresa] = $this->crearSolicitud('B10000003', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $empresa->id)
            ->assertJsonStructure(['data' => ['empresa', 'responsable', 'documentos', 'historial', 'estado_actual']]);
    }

    public function test_admin_puede_previsualizar_pdf_y_no_expone_path_real(): void
    {
        Storage::fake('verificaciones');
        [, , $solicitud] = $this->crearSolicitud('B10000004', 'sociedad');
        Storage::disk('verificaciones')->put('solicitudes/documento.pdf', '%PDF-1.4 test');

        $documento = DocumentoVerificacion::query()->create([
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'documento_fiscal',
            'archivo' => 'solicitudes/documento.pdf',
            'nombre_original' => 'documento.pdf',
            'mime_type' => 'application/pdf',
            'tamano' => 13,
        ]);

        $response = $this->actingAs($this->crearAdmin(), 'sanctum')
            ->get("/api/v1/admin/documentos-verificacion/{$documento->id}/preview")
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="documento.pdf"');

        $this->assertStringNotContainsString('storage/app', $response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString('solicitudes/documento.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_puede_previsualizar_imagen(): void
    {
        Storage::fake('verificaciones');
        [, , $solicitud] = $this->crearSolicitud('B10000005', 'sociedad');
        Storage::disk('verificaciones')->put('solicitudes/imagen.png', 'png-content');

        $documento = DocumentoVerificacion::query()->create([
            'solicitud_verificacion_id' => $solicitud->id,
            'tipo_documento' => 'dni_frontal',
            'archivo' => 'solicitudes/imagen.png',
            'nombre_original' => 'imagen.png',
            'mime_type' => 'image/png',
            'tamano' => 11,
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->get("/api/v1/admin/documentos-verificacion/{$documento->id}/preview")
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="imagen.png"');
    }

    public function test_admin_puede_aprobar_solicitud_y_activa_usuario(): void
    {
        [$user, $empresa] = $this->crearSolicitud('B10000006', 'sociedad');

        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/fases/identidad/aprobar")
            ->assertOk()
            ->assertJsonPath('data.estado_identidad', 'aprobada');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/fases/empresa/aprobar")
            ->assertOk()
            ->assertJsonPath('data.estado_empresa', 'aprobada');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/fases/representacion/aprobar")
            ->assertOk()
            ->assertJsonPath('data.estado_representacion', 'aprobada');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar", [
                'observaciones' => 'Documentacion correcta.',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_actual', 'aprobada');

        $this->assertTrue($user->fresh()->activo);
        $this->assertTrue($empresa->fresh()->activa);
        $this->assertDatabaseHas('admin_verificacion_eventos', ['accion' => 'aprobar_fase_identidad']);
        $this->assertDatabaseHas('admin_verificacion_eventos', ['accion' => 'aprobar_solicitud']);
    }

    public function test_no_se_puede_aprobar_solicitud_con_fases_pendientes(): void
    {
        [, $empresa] = $this->crearSolicitud('B10000012', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar")
            ->assertStatus(422);
    }

    public function test_admin_puede_aprobar_autonomo_sin_representacion(): void
    {
        [$user, $empresa, $solicitud] = $this->crearSolicitud('12345678Z', 'autonomo');
        $solicitud->update([
            'estado_identidad' => 'aprobada',
            'estado_empresa' => 'aprobada',
            'estado_representacion' => null,
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar")
            ->assertOk()
            ->assertJsonPath('data.estado_actual', 'aprobada');

        $this->assertTrue($user->fresh()->activo);
        $this->assertTrue($empresa->fresh()->activa);
        $this->assertDatabaseHas('verificaciones_usuario', [
            'user_id' => $user->id,
            'estado_verificacion_id' => $this->estadoId('aprobada'),
        ]);
        $this->assertDatabaseHas('verificaciones_empresa', [
            'empresa_id' => $empresa->id,
            'estado_verificacion_id' => $this->estadoId('aprobada'),
        ]);
    }

    public function test_admin_no_puede_aprobar_sociedad_sin_representacion_aprobada(): void
    {
        [, $empresa, $solicitud] = $this->crearSolicitud('B10000014', 'sociedad');
        $solicitud->update([
            'estado_identidad' => 'aprobada',
            'estado_empresa' => 'aprobada',
            'estado_representacion' => 'pendiente',
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar")
            ->assertStatus(422);
    }

    public function test_admin_puede_aprobar_sociedad_con_representacion_aprobada(): void
    {
        [$user, $empresa, $solicitud] = $this->crearSolicitud('B10000015', 'sociedad');
        $solicitud->update([
            'estado_identidad' => 'aprobada',
            'estado_empresa' => 'aprobada',
            'estado_representacion' => 'aprobada',
        ]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar")
            ->assertOk()
            ->assertJsonPath('data.estado_actual', 'aprobada');

        $this->assertTrue($user->fresh()->activo);
        $this->assertTrue($empresa->fresh()->activa);
    }

    public function test_no_se_puede_aprobar_una_solicitud_rechazada(): void
    {
        [, $empresa, $solicitud] = $this->crearSolicitud('B10000007', 'sociedad');
        $solicitud->update(['estado_verificacion_id' => $this->estadoId('rechazada')]);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/aprobar")
            ->assertStatus(422);
    }

    public function test_admin_puede_rechazar_solicitud_con_motivo(): void
    {
        [$user, $empresa] = $this->crearSolicitud('B10000008', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/rechazar", [
                'motivo' => 'No se acredita representacion suficiente.',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_actual', 'rechazada');

        $this->assertFalse($user->fresh()->activo);
        $this->assertDatabaseHas('admin_verificacion_eventos', [
            'accion' => 'rechazar_solicitud',
            'motivo' => 'No se acredita representacion suficiente.',
        ]);
    }

    public function test_no_se_puede_rechazar_sin_motivo(): void
    {
        [, $empresa] = $this->crearSolicitud('B10000009', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/rechazar")
            ->assertStatus(422);
    }

    public function test_admin_puede_solicitar_subsanacion(): void
    {
        [$user, $empresa] = $this->crearSolicitud('B10000010', 'sociedad');

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson("/api/v1/admin/solicitudes-verificacion/{$empresa->id}/solicitar-subsanacion", [
                'motivo' => 'Debe aportar documento actualizado de representacion.',
                'documentos_requeridos' => ['representacion'],
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_actual', 'subsanacion');

        $this->assertFalse($user->fresh()->activo);
        $this->assertDatabaseHas('admin_verificacion_eventos', ['accion' => 'solicitar_subsanacion']);
    }

    public function test_endpoint_moderno_no_debe_usar_id_de_solicitud(): void
    {
        Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal' => 'Empresa sin solicitud',
            'nif' => 'B99999990',
            'activa' => false,
        ]);

        [, $empresa, $solicitud] = $this->crearSolicitud('B10000011', 'sociedad');

        $this->assertNotSame($empresa->id, $solicitud->id);

        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson("/api/v1/admin/solicitudes-verificacion/{$solicitud->id}")
            ->assertNotFound();
    }

    public function test_rutas_legacy_de_solicitudes_admin_no_existen(): void
    {
        [, , $solicitud] = $this->crearSolicitud('B10000013', 'sociedad');
        $admin = $this->crearAdmin();

        foreach ([
            '/api/v1/admin/solicitudes',
            "/api/v1/admin/solicitudes/{$solicitud->id}",
            '/api/v1/admin/solicitudes-registro',
            "/api/v1/admin/solicitudes-registro/{$solicitud->id}",
            "/api/v1/admin/documentos/{$solicitud->id}/descargar",
            "/api/v1/admin/documentos-verificacion/{$solicitud->id}/ver",
        ] as $url) {
            $this->actingAs($admin, 'sanctum')
                ->getJson($url)
                ->assertNotFound();
        }

        foreach ([
            "/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-identidad",
            "/api/v1/admin/solicitudes/{$solicitud->id}/rechazar-identidad",
            "/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-empresa",
            "/api/v1/admin/solicitudes/{$solicitud->id}/rechazar-empresa",
            "/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-representacion",
            "/api/v1/admin/solicitudes/{$solicitud->id}/rechazar-representacion",
            "/api/v1/admin/solicitudes/{$solicitud->id}/aprobar-total",
            "/api/v1/admin/solicitudes/{$solicitud->id}/rechazar-total",
            "/api/v1/admin/solicitudes-registro/{$solicitud->id}/aprobar",
            "/api/v1/admin/solicitudes-registro/{$solicitud->id}/rechazar",
        ] as $url) {
            $this->actingAs($admin, 'sanctum')
                ->postJson($url)
                ->assertNotFound();
        }
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
            'nombre' => 'Responsable',
            'apellido1' => $nif,
            'empresa_id' => $empresa->id,
            'role_id' => $this->roleId('titular'),
            'activo' => false,
        ]);

        VerificacionUsuario::query()->create([
            'user_id' => $user->id,
            'estado_verificacion_id' => $this->estadoId('pendiente'),
            'tipo_documento_identidad_id' => (int) DB::table('tipos_documento_identidad')->value('id'),
            'numero_documento' => '12345678A',
            'ruta_documento_anverso' => 'privado/no-expuesto.pdf',
        ]);

        VerificacionEmpresa::query()->create([
            'empresa_id' => $empresa->id,
            'estado_verificacion_id' => $this->estadoId('pendiente'),
            'ruta_documento_fiscal' => 'privado/no-expuesto.pdf',
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

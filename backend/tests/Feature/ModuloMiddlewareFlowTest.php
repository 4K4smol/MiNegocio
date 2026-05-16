<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuloMiddlewareFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_usuario_con_modulo_activo_puede_acceder(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B20000001');

        app(ModuloService::class)->activarModulo((int) $user->empresa_id, 'clientes');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertOk();
    }

    public function test_usuario_sin_modulo_recibe_403(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B20000002');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden()
            ->assertJsonPath(
                'message',
                'La empresa no tiene activo el módulo requerido: clientes.'
            );
    }

    public function test_admin_es_bloqueado_en_rutas_crm_de_empresa(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_activar_modulo_permite_acceso(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B20000003');
        $admin = $this->crearAdmin();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/empresas/{$empresa->id}/modulos/clientes/activar")
            ->assertOk()
            ->assertJsonPath('data.activo', true);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertOk();
    }

    public function test_desactivar_modulo_bloquea_acceso(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B20000004');
        $admin = $this->crearAdmin();

        app(ModuloService::class)->activarModulo((int) $empresa->id, 'clientes');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/empresas/{$empresa->id}/modulos/clientes/desactivar")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden()
            ->assertJsonPath(
                'message',
                'La empresa no tiene activo el módulo requerido: clientes.'
            );
    }

    public function test_usuario_no_admin_no_puede_acceder_a_modulos(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B20000005');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/modulos')
            ->assertForbidden();
    }

    public function test_usuario_no_admin_no_puede_acceder_a_roles(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B20000006');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/roles')
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Empresa}
     */
    private function crearUsuarioEmpresa(string $nif): array
    {
        $empresa = Empresa::unguarded(function () use ($nif): Empresa {
            return Empresa::query()->create($this->datosEmpresa($nif));
        });

        $user = User::factory()->create(array_merge(
            [
                'empresa_id' => $empresa->id,
            ],
            $this->datosRolUsuario(['empresa', 'cliente', 'usuario'], 2),
            $this->datosUsuarioActivo()
        ));

        return [$user, $empresa];
    }

    private function crearAdmin(): User
    {
        return User::factory()->create(array_merge(
            [
                'empresa_id' => null,
            ],
            $this->datosRolUsuario(['admin', 'administrador'], 1),
            $this->datosUsuarioActivo()
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function datosEmpresa(string $nif): array
    {
        $datos = [
            'tipo_empresa_id' => $this->catalogoId('tipos_empresa', ['sociedad', 'empresa', 'sl'], 1),
            'nombre_fiscal' => 'Empresa '.$nif,
            'nif' => $nif,
        ];

        if (Schema::hasColumn('empresas', 'nombre_comercial')) {
            $datos['nombre_comercial'] = 'Comercial '.$nif;
        }

        if (Schema::hasColumn('empresas', 'correo')) {
            $datos['correo'] = strtolower($nif).'@example.com';
        }

        if (Schema::hasColumn('empresas', 'email')) {
            $datos['email'] = strtolower($nif).'@example.com';
        }

        if (Schema::hasColumn('empresas', 'telefono')) {
            $datos['telefono'] = '600000000';
        }

        if (Schema::hasColumn('empresas', 'estado_verificacion_id')) {
            $datos['estado_verificacion_id'] = $this->catalogoId(
                'estados_verificacion',
                ['aprobada', 'aprobado', 'verificada'],
                1
            );
        }

        if (Schema::hasColumn('empresas', 'activa')) {
            $datos['activa'] = true;
        }

        if (Schema::hasColumn('empresas', 'activo')) {
            $datos['activo'] = true;
        }

        return $datos;
    }

    /**
     * @param array<int, string> $nombres
     * @return array<string, int>
     */
    private function datosRolUsuario(array $nombres, int $fallbackId): array
    {
        return [
            $this->campoRolUsuario() => $this->rolId($nombres, $fallbackId),
        ];
    }

    private function campoRolUsuario(): string
    {
        if (Schema::hasColumn('users', 'role_id')) {
            return 'role_id';
        }

        if (Schema::hasColumn('users', 'rol_id')) {
            return 'rol_id';
        }

        return 'role_id';
    }

    /**
     * @param array<int, string> $nombres
     */
    private function rolId(array $nombres, int $fallbackId): int
    {
        if (! Schema::hasTable('roles')) {
            return $fallbackId;
        }

        foreach ($nombres as $nombre) {
            $query = DB::table('roles');

            $query->where(function ($q) use ($nombre): void {
                if (Schema::hasColumn('roles', 'slug')) {
                    $q->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($nombre)]);
                }

                if (Schema::hasColumn('roles', 'nombre')) {
                    $q->orWhereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)]);
                }

                if (Schema::hasColumn('roles', 'name')) {
                    $q->orWhereRaw('LOWER(name) = ?', [mb_strtolower($nombre)]);
                }
            });

            $id = $query->value('id');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return $fallbackId;
    }

    /**
     * @return array<string, mixed>
     */
    private function datosUsuarioActivo(): array
    {
        $datos = [];

        if (Schema::hasColumn('users', 'activo')) {
            $datos['activo'] = true;
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $datos['email_verified_at'] = now();
        }

        return $datos;
    }

    /**
     * @param array<int, string> $nombres
     */
    private function catalogoId(string $tabla, array $nombres, int $fallbackId): int
    {
        if (! Schema::hasTable($tabla)) {
            return $fallbackId;
        }

        foreach ($nombres as $nombre) {
            $query = DB::table($tabla);

            $query->where(function ($q) use ($tabla, $nombre): void {
                if (Schema::hasColumn($tabla, 'slug')) {
                    $q->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($nombre)]);
                }

                if (Schema::hasColumn($tabla, 'codigo')) {
                    $q->orWhereRaw('LOWER(codigo) = ?', [mb_strtolower($nombre)]);
                }

                if (Schema::hasColumn($tabla, 'nombre')) {
                    $q->orWhereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)]);
                }
            });

            $id = $query->value('id');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return $fallbackId;
    }
}

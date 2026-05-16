<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClienteEmpresaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_usuario_empresa_crea_cliente_y_empresa_id_se_asigna_automaticamente(): void
    {
        [$usuario, $empresa] = $this->crearUsuarioConEmpresa('B50000001');

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Carlos',
                'apellidos'       => 'García Pérez',
                'dni_cif'         => 'X1111111A',
            ])
            ->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id);

        $this->assertDatabaseHas('clientes', [
            'dni_cif'    => 'X1111111A',
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_empresa_id_enviado_por_el_frontend_es_ignorado(): void
    {
        [$usuario, $empresa] = $this->crearUsuarioConEmpresa('B50000002');

        $otraEmpresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal'   => 'Empresa Ajena SA',
            'nif'             => 'B50000099',
            'activa'          => true,
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Laura',
                'apellidos'       => 'Martínez',
                'dni_cif'         => 'X2222222B',
                'empresa_id'      => $otraEmpresa->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id);
    }

    public function test_admin_no_puede_crear_clientes(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Test Admin',
                'dni_cif'         => 'X3333333C',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_admin_no_puede_listar_clientes(): void
    {
        $this->actingAs($this->crearAdmin(), 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertForbidden()
            ->assertJsonPath('message', 'El administrador global no puede acceder al CRM de empresa.');
    }

    public function test_dni_cif_duplicado_en_la_misma_empresa_devuelve_422(): void
    {
        [$usuario] = $this->crearUsuarioConEmpresa('B50000003');

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Pedro',
                'dni_cif'         => 'X4444444D',
            ])
            ->assertCreated();

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Otro Pedro',
                'dni_cif'         => 'X4444444D',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.dni_cif.0', 'Ya existe un cliente con este DNI/CIF en tu empresa.');
    }

    public function test_mismo_dni_cif_en_distinta_empresa_esta_permitido(): void
    {
        [$usuario1] = $this->crearUsuarioConEmpresa('B50000004');
        [$usuario2] = $this->crearUsuarioConEmpresa('B50000005');

        $this->actingAs($usuario1, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Ana',
                'dni_cif'         => 'X5555555E',
            ])
            ->assertCreated();

        $this->actingAs($usuario2, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Ana Copia',
                'dni_cif'         => 'X5555555E',
            ])
            ->assertCreated();
    }

    public function test_usuario_solo_ve_clientes_de_su_empresa(): void
    {
        [$usuario1, $empresa1] = $this->crearUsuarioConEmpresa('B50000006');
        [$usuario2] = $this->crearUsuarioConEmpresa('B50000007');

        $this->actingAs($usuario1, 'sanctum')
            ->postJson('/api/v1/clientes', [
                'tipo_cliente_id' => $this->tipoClienteId(),
                'nombre'          => 'Cliente Privado',
                'dni_cif'         => 'X6666666F',
            ])
            ->assertCreated();

        $respuesta = $this->actingAs($usuario2, 'sanctum')
            ->getJson('/api/v1/clientes')
            ->assertOk();

        $ids = collect($respuesta->json('data.data'))->pluck('empresa_id')->unique();
        $this->assertNotContains($empresa1->id, $ids->all());
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id'    => $this->roleId('admin'),
            'activo'     => true,
        ]);
    }

    /**
     * @return array{0: User, 1: Empresa}
     */
    private function crearUsuarioConEmpresa(string $nif): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => $this->tipoEmpresaId('sociedad'),
            'nombre_fiscal'   => 'Empresa ' . $nif,
            'nif'             => $nif,
            'activa'          => true,
        ]);

        $usuario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id'    => $this->roleId('titular'),
            'activo'     => true,
        ]);

        app(ModuloService::class)->activarModulo((int) $empresa->id, 'clientes');

        return [$usuario, $empresa];
    }

    private function roleId(string $nombre): int
    {
        return (int) DB::table('roles')->where('nombre', $nombre)->value('id');
    }

    private function tipoEmpresaId(string $nombre): int
    {
        return (int) DB::table('tipos_empresa')->where('nombre', $nombre)->value('id');
    }

    private function tipoClienteId(): int
    {
        return (int) DB::table('tipos_cliente')->value('id');
    }
}

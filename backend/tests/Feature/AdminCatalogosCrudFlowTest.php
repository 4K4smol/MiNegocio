<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCatalogosCrudFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_edita_catalogos_con_codigo_sin_falso_duplicado(): void
    {
        $admin = $this->crearAdmin();

        foreach ($this->catalogosConCodigo() as $catalogo) {
            $codigo = $catalogo['codigo'].'_a';
            $id = $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], $this->payload($catalogo, $codigo, 'Nombre A'))
                ->assertCreated()
                ->json('data.id');

            $this->actingAs($admin, 'sanctum')
                ->putJson("{$catalogo['endpoint']}/{$id}", $this->payload($catalogo, $codigo, 'Nombre A editado'))
                ->assertOk()
                ->assertJsonPath('data.codigo', $codigo);
        }
    }

    public function test_admin_no_puede_editar_catalogo_con_codigo_de_otro_registro(): void
    {
        $admin = $this->crearAdmin();

        foreach ($this->catalogosConCodigo() as $catalogo) {
            $codigoA = $catalogo['codigo'].'_dup_a';
            $codigoB = $catalogo['codigo'].'_dup_b';

            $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], $this->payload($catalogo, $codigoA, 'Nombre A'))
                ->assertCreated();

            $idB = $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], $this->payload($catalogo, $codigoB, 'Nombre B'))
                ->assertCreated()
                ->json('data.id');

            $this->actingAs($admin, 'sanctum')
                ->putJson("{$catalogo['endpoint']}/{$idB}", ['codigo' => $codigoA])
                ->assertStatus(422);
        }
    }

    public function test_admin_edita_catalogos_sin_codigo_sin_falso_duplicado(): void
    {
        $admin = $this->crearAdmin();

        foreach ($this->catalogosSinCodigo() as $catalogo) {
            $nombre = $catalogo['nombre'].' A';
            $id = $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], [
                    'nombre' => $nombre,
                    'descripcion' => 'Descripcion inicial',
                ])
                ->assertCreated()
                ->json('data.id');

            $this->actingAs($admin, 'sanctum')
                ->putJson("{$catalogo['endpoint']}/{$id}", [
                    'nombre' => $nombre,
                    'descripcion' => 'Descripcion editada',
                ])
                ->assertOk()
                ->assertJsonPath('data.nombre', $nombre);
        }
    }

    public function test_admin_no_puede_editar_catalogo_sin_codigo_con_nombre_de_otro_registro(): void
    {
        $admin = $this->crearAdmin();

        foreach ($this->catalogosSinCodigo() as $catalogo) {
            $nombreA = $catalogo['nombre'].' duplicado A';
            $nombreB = $catalogo['nombre'].' duplicado B';

            $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], [
                    'nombre' => $nombreA,
                    'descripcion' => 'Descripcion A',
                ])
                ->assertCreated();

            $idB = $this->actingAs($admin, 'sanctum')
                ->postJson($catalogo['endpoint'], [
                    'nombre' => $nombreB,
                    'descripcion' => 'Descripcion B',
                ])
                ->assertCreated()
                ->json('data.id');

            $this->actingAs($admin, 'sanctum')
                ->putJson("{$catalogo['endpoint']}/{$idB}", ['nombre' => $nombreA])
                ->assertStatus(422);
        }
    }

    /**
     * @return array<int, array{endpoint: string, codigo: string, orden_min: int}>
     */
    private function catalogosConCodigo(): array
    {
        return [
            ['endpoint' => '/api/v1/tipos-cliente', 'codigo' => 'tc_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/tipos-localizacion-cliente', 'codigo' => 'tl_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/tipos-evento-facturacion', 'codigo' => 'tef_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/tipos-factura', 'codigo' => 'tf_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/tipos-inventario-movimiento', 'codigo' => 'tim_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/inventario-unidades-medida', 'codigo' => 'ium_test', 'orden_min' => 0],
            ['endpoint' => '/api/v1/tipos-rectificacion', 'codigo' => 'tr_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/tipos-registro-facturacion', 'codigo' => 'trf_test', 'orden_min' => 1],
            ['endpoint' => '/api/v1/admin/tipos-tarifa-servicio', 'codigo' => 'tts_test', 'orden_min' => 0],
        ];
    }

    /**
     * @return array<int, array{endpoint: string, nombre: string}>
     */
    private function catalogosSinCodigo(): array
    {
        return [
            ['endpoint' => '/api/v1/tipos-empresa', 'nombre' => 'Tipo Empresa Test'],
            ['endpoint' => '/api/v1/tipos-documento-identidad', 'nombre' => 'Tipo Documento Test'],
        ];
    }

    /**
     * @param array{endpoint: string, codigo: string, orden_min: int} $catalogo
     */
    private function payload(array $catalogo, string $codigo, string $nombre): array
    {
        $payload = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => 'Descripcion '.$nombre,
        ];

        if ($catalogo['endpoint'] !== '/api/v1/inventario-unidades-medida') {
            $payload['activo'] = true;
            $payload['orden'] = $catalogo['orden_min'];
        }

        return $payload;
    }

    private function crearAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id' => (int) DB::table('roles')->where('nombre', 'admin')->value('id'),
            'activo' => true,
        ]);
    }
}


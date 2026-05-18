<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\InventarioItem;
use App\Models\InventarioUbicacion;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarioEmpresaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_empresa_no_puede_crear_item_en_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B33000001');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B33000002');
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-items', [
            'empresa_id' => $otraEmpresa->id,
            'unidad_medida_id' => $this->unidadId(),
            'nombre' => 'Producto propio',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.empresa_id', $user->empresa_id);

        $this->assertDatabaseMissing('inventario_items', [
            'empresa_id' => $otraEmpresa->id,
            'nombre' => 'Producto propio',
        ]);
    }

    public function test_empresa_puede_crear_item_simple(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000015');

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-items', [
            'unidad_medida_id' => $this->unidadId(),
            'nombre' => 'Item simple',
        ])->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id);

        $this->assertDatabaseHas('inventario_items', [
            'empresa_id' => $empresa->id,
            'nombre' => 'Item simple',
        ]);
    }

    public function test_listado_muestra_items(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000017');
        $item = $this->crearItem($empresa->id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/inventario-items?per_page=100')
            ->assertOk()
            ->assertJsonFragment(['id' => $item->id]);
    }

    public function test_empresa_no_puede_usar_ubicacion_de_otra_empresa(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B33000005');
        [, $otraEmpresa] = $this->crearUsuarioEmpresa('B33000006');
        $ubicacionAjena = $this->crearUbicacion($otraEmpresa->id, 'Ajena');

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-items', [
            'unidad_medida_id' => $this->unidadId(),
            'ubicacion_id' => $ubicacionAjena->id,
            'nombre' => 'Producto bloqueado',
        ])->assertStatus(422);
    }

    public function test_entrada_aumenta_stock(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000007');
        $item = $this->crearItem($empresa->id, stock: 10);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('entrada'),
            'cantidad' => 5,
        ])->assertCreated()
            ->assertJsonPath('data.stock_anterior', '10.00')
            ->assertJsonPath('data.stock_posterior', '15.00');

        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'stock_actual' => 15]);
    }

    public function test_movimientos_funcionan_en_item_simple(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000018');
        $item = $this->crearItem($empresa->id, stock: 2);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('entrada'),
            'cantidad' => 3,
        ])->assertCreated()
            ->assertJsonPath('data.stock_posterior', '5.00');

        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'stock_actual' => 5]);
    }

    public function test_salida_reduce_stock(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000008');
        $item = $this->crearItem($empresa->id, stock: 10);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('salida'),
            'cantidad' => 4,
        ])->assertCreated()
            ->assertJsonPath('data.stock_posterior', '6.00');

        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'stock_actual' => 6]);
    }

    public function test_salida_no_permite_stock_negativo(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000009');
        $item = $this->crearItem($empresa->id, stock: 3);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('salida'),
            'cantidad' => 4,
        ])->assertStatus(422);

        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'stock_actual' => 3]);
    }

    public function test_ajuste_fija_stock_posterior(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000010');
        $item = $this->crearItem($empresa->id, stock: 10);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('ajuste'),
            'cantidad' => 0,
            'stock_posterior' => 7,
        ])->assertCreated()
            ->assertJsonPath('data.cantidad', '-3.00')
            ->assertJsonPath('data.stock_posterior', '7.00');

        $this->assertDatabaseHas('inventario_items', ['id' => $item->id, 'stock_actual' => 7]);
    }

    public function test_traslado_no_cambia_stock_y_actualiza_ubicacion(): void
    {
        [$user, $empresa] = $this->crearUsuarioEmpresa('B33000011');
        $origen = $this->crearUbicacion($empresa->id, 'Almacen A');
        $destino = $this->crearUbicacion($empresa->id, 'Almacen B');
        $item = $this->crearItem($empresa->id, stock: 10, ubicacionId: $origen->id);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-movimientos', [
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('traslado'),
            'cantidad' => 2,
            'ubicacion_origen_id' => $origen->id,
            'ubicacion_destino_id' => $destino->id,
        ])->assertCreated()
            ->assertJsonPath('data.stock_posterior', '10.00');

        $this->assertDatabaseHas('inventario_items', [
            'id' => $item->id,
            'stock_actual' => 10,
            'ubicacion_id' => $destino->id,
        ]);
    }

    public function test_admin_puede_operar_sobre_cualquier_empresa(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [, $empresa] = $this->crearUsuarioEmpresa('B33000012');
        $item = $this->crearItem($empresa->id, stock: 1);

        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/inventario-movimientos', [
            'empresa_id' => $empresa->id,
            'inventario_item_id' => $item->id,
            'tipo_movimiento_id' => $this->tipoMovimientoId('entrada'),
            'cantidad' => 4,
        ])->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id)
            ->assertJsonPath('data.stock_posterior', '5.00');
    }

    public function test_usuario_sin_modulo_inventario_no_puede_acceder(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B33000013', false);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/inventario-items')
            ->assertForbidden();
    }

    public function test_empresa_no_puede_crear_unidades_globales(): void
    {
        [$user] = $this->crearUsuarioEmpresa('B33000014');

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/inventario-unidades-medida', [
            'codigo' => 'cj',
            'nombre' => 'Caja',
        ])->assertForbidden();
    }

    private function crearUsuarioAdmin(): User
    {
        return User::factory()->create([
            'empresa_id' => null,
            'role_id' => (int) DB::table('roles')->where('nombre', 'admin')->value('id'),
            'activo' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: Empresa}
     */
    private function crearUsuarioEmpresa(string $nif, bool $activarModulo = true): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => (int) DB::table('tipos_empresa')->value('id'),
            'nombre_fiscal' => 'Empresa '.$nif,
            'nombre_comercial' => 'Comercial '.$nif,
            'nif' => $nif,
            'correo' => strtolower($nif).'@example.com',
            'telefono' => '600000000',
            'activa' => true,
        ]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_id' => (int) DB::table('roles')->where('nombre', 'titular')->value('id'),
            'activo' => true,
        ]);

        if ($activarModulo) {
            app(ModuloService::class)->activarModulo((int) $empresa->id, 'inventario');
        }

        return [$user, $empresa];
    }

    private function crearUbicacion(int $empresaId, string $nombre = 'Almacen'): InventarioUbicacion
    {
        return InventarioUbicacion::query()->create([
            'empresa_id' => $empresaId,
            'nombre' => $nombre,
            'activo' => true,
        ]);
    }

    private function crearItem(int $empresaId, int $stock = 0, ?int $ubicacionId = null): InventarioItem
    {
        return InventarioItem::query()->create([
            'empresa_id' => $empresaId,
            'unidad_medida_id' => $this->unidadId(),
            'ubicacion_id' => $ubicacionId,
            'nombre' => 'Producto '.uniqid(),
            'sku' => 'SKU'.uniqid(),
            'stock_actual' => $stock,
            'stock_minimo' => 0,
            'coste_unitario' => 2,
            'activo' => true,
        ]);
    }

    private function unidadId(): int
    {
        return (int) DB::table('inventario_unidades_medida')->where('codigo', 'ud')->value('id');
    }

    private function tipoMovimientoId(string $codigo): int
    {
        return (int) DB::table('tipos_inventario_movimiento')->where('codigo', $codigo)->value('id');
    }
}

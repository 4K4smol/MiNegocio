<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\LocalizacionCliente;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use App\Models\TipoLocalizacionCliente;
use App\Models\TipoTarifaServicio;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrdenTrabajoCreacionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_empresa_crea_orden_con_localizacion_precio_y_evento_de_calendario(): void
    {
        [$user, $cliente, $localizacion, $servicio, $precio] = $this->crearContexto();

        $inicio = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i:s');
        $fin = now()->addDay()->setTime(11, 30)->format('Y-m-d\TH:i:s');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ordenes-trabajo', [
                'cliente_id' => $cliente->id,
                'localizacion_cliente_id' => $localizacion->id,
                'prioridad_codigo' => 'alta',
                'fecha_programada_inicio' => $inicio,
                'fecha_programada_fin' => $fin,
                'notas_cliente' => 'Preparar acceso al cuadro electrico.',
                'lineas' => [[
                    'servicio_id' => $servicio->id,
                    'servicio_precio_id' => $precio->id,
                    'tipo_tarifa_servicio_id' => $precio->tipo_tarifa_servicio_id,
                    'descripcion' => 'Arreglo electrico urgente',
                    'observaciones' => 'Llevar material de repuesto.',
                    'cantidad' => 2,
                    'precio_unitario' => 80,
                    'descuento_porcentaje' => 10,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.localizacion_cliente_id', $localizacion->id)
            ->assertJsonPath('data.estado', 'programada')
            ->assertJsonPath('data.prioridad', 'alta')
            ->assertJsonPath('data.totales.subtotal', 144)
            ->assertJsonPath('data.totales.cuota_iva', 30.24)
            ->assertJsonPath('data.totales.total', 174.24)
            ->assertJsonPath('data.lineas.0.meta.servicio_precio_id', $precio->id);

        $ordenId = $response->json('data.id');

        $this->assertDatabaseHas('calendario_eventos', [
            'empresa_id' => $user->empresa_id,
            'orden_trabajo_id' => $ordenId,
            'cliente_id' => $cliente->id,
            'tipo' => 'ORDEN_TRABAJO',
            'ubicacion' => $localizacion->direccion_completa,
        ]);
    }

    public function test_empresa_no_puede_usar_localizacion_de_otro_cliente(): void
    {
        [$user, $cliente, , $servicio, $precio] = $this->crearContexto();
        [, , $localizacionAjena] = $this->crearContexto('B99900002');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ordenes-trabajo', [
                'cliente_id' => $cliente->id,
                'localizacion_cliente_id' => $localizacionAjena->id,
                'lineas' => [[
                    'servicio_id' => $servicio->id,
                    'servicio_precio_id' => $precio->id,
                    'cantidad' => 1,
                    'precio_unitario' => 80,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('localizacion_cliente_id');
    }

    public function test_numeracion_de_orden_usa_id_autoincremental(): void
    {
        [$user, $cliente, , $servicio, $precio] = $this->crearContexto();

        $payload = [
            'cliente_id' => $cliente->id,
            'lineas' => [[
                'servicio_id' => $servicio->id,
                'servicio_precio_id' => $precio->id,
                'cantidad' => 1,
                'precio_unitario' => 80,
                'iva_porcentaje' => 21,
            ]],
        ];

        $first = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ordenes-trabajo', $payload)
            ->assertCreated()
            ->json('data.numero');

        $second = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/ordenes-trabajo', $payload)
            ->assertCreated()
            ->json('data.numero');

        $this->assertMatchesRegularExpression('/^OT-'.now()->format('Ymd').'-\\d{6}$/', $first);
        $this->assertMatchesRegularExpression('/^OT-'.now()->format('Ymd').'-\\d{6}$/', $second);
        $this->assertNotSame($first, $second);
        $this->assertFalse(Schema::hasTable('orden_trabajo_contadores'));
    }

    private function crearContexto(string $nif = 'B99900001'): array
    {
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa '.$nif,
            'nif' => $nif,
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'ordenes');
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'calendario');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => 1,
            'nombre' => 'Cliente '.$nif,
            'dni_cif' => 'C'.$nif,
            'activo' => true,
        ]);

        $localizacion = LocalizacionCliente::query()->create([
            'cliente_id' => $cliente->id,
            'nombre_localizacion' => 'Sede principal',
            'tipo_localizacion_id' => TipoLocalizacionCliente::query()->firstOrFail()->id,
            'direccion' => 'Calle Mayor 1',
            'ciudad' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'España',
            'es_principal' => true,
            'activo' => true,
        ]);

        $servicio = Servicio::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'SERV-'.$nif,
            'nombre' => 'Arreglo electrico',
            'unidad_servicio' => 'hora',
            'duracion_estimada_min' => 60,
            'activo' => true,
        ]);

        $precio = ServicioPrecio::query()->create([
            'servicio_id' => $servicio->id,
            'tipo_tarifa_servicio_id' => TipoTarifaServicio::query()->where('codigo', 'urgente')->firstOrFail()->id,
            'precio_base' => 80,
            'iva_porcentaje' => 21,
            'moneda' => 'EUR',
        ]);

        return [$user, $cliente, $localizacion, $servicio, $precio];
    }
}

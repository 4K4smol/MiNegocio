<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\TipoCliente;
use App\Models\User;
use App\Services\ModuloService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Flujo CRUD y fiscal completo de facturas vía API.
 *
 * Cubre: borrador → emitir → registros técnicos → anular / rectificar.
 * También valida las restricciones: no editar emitidas, no anular borradores,
 * no anular sin registro de alta, no rectificar estados inválidos.
 */
class FacturaFlujoCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // -------------------------------------------------------------------------
    // BORRADOR
    // -------------------------------------------------------------------------

    public function test_crear_factura_como_borrador(): void
    {
        [$user, $cliente] = $this->contexto();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas', $this->payloadBase($cliente))
            ->assertCreated()
            ->assertJsonPath('data.estado_factura', 'borrador')
            ->assertJsonPath('data.numero', null);

        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $cliente->id,
            'empresa_id' => $user->empresa_id,
        ]);
        $this->assertDatabaseMissing('registros_facturacion', ['empresa_id' => $user->empresa_id]);
    }

    public function test_editar_factura_en_borrador(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/empresa/facturas/' . $factura->id, [
                'observaciones' => 'Texto actualizado',
                'lineas' => [[
                    'descripcion' => 'Servicio actualizado',
                    'cantidad' => 3,
                    'precio_unitario' => 50,
                    'iva_porcentaje' => 21,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.total', 181.5);
    }

    public function test_no_se_puede_editar_factura_emitida(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/empresa/facturas/' . $factura->id, [
                'observaciones' => 'Intento de edición ilegal',
            ])
            ->assertStatus(500);
    }

    public function test_eliminar_borrador_sin_registros(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/empresa/facturas/' . $factura->id)
            ->assertOk();

        // El modelo usa SoftDeletes: el registro queda con deleted_at relleno
        $this->assertSoftDeleted('facturas', ['id' => $factura->id]);
    }

    public function test_no_se_puede_eliminar_factura_emitida(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/empresa/facturas/' . $factura->id)
            ->assertStatus(500);
    }

    public function test_no_se_puede_eliminar_factura_con_registros_tecnicos(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        // Aunque no debería llegarse aquí por el check de estado, lo verificamos explícitamente
        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/empresa/facturas/' . $factura->id)
            ->assertStatus(500);

        $this->assertDatabaseHas('facturas', ['id' => $factura->id]);
    }

    // -------------------------------------------------------------------------
    // EMITIR
    // -------------------------------------------------------------------------

    public function test_emitir_factura_genera_numero_y_registro_de_alta(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/emitir')
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'emitida');

        $this->assertNotNull($response->json('data.numero'));
        $this->assertNotNull($response->json('data.ultimo_registro_facturacion_hash'));
        $this->assertDatabaseHas('registros_facturacion', ['factura_id' => $factura->id]);
        $this->assertDatabaseHas('registros_evento_facturacion', [
            'factura_id' => $factura->id,
            'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_CREADO',
        ]);
    }

    // -------------------------------------------------------------------------
    // ANULAR
    // -------------------------------------------------------------------------

    public function test_anular_factura_emitida_con_registro_de_alta(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', [
                'motivo_anulacion' => 'Cliente solicita anulación',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'anulada');

        $this->assertDatabaseHas('registros_evento_facturacion', [
            'factura_id' => $factura->id,
            'codigo_evento' => 'REGISTRO_FACTURACION_ANULACION_CREADO',
        ]);
    }

    public function test_no_se_puede_anular_factura_en_borrador(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', [
                'motivo_anulacion' => 'Intento ilegal',
            ])
            ->assertStatus(500);

        $this->assertDatabaseHas('facturas', ['id' => $factura->id, 'estado_factura_id' => $factura->estado_factura_id]);
    }

    public function test_no_se_puede_anular_factura_sin_registro_de_alta(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        // Cambiamos el estado a emitida manualmente SIN generar registro de alta
        // para simular un caso de inconsistencia o una factura no fiscal
        $estado_emitida_id = \App\Models\EstadoFactura::query()->where('codigo', 'emitida')->value('id');
        $factura->estado_factura_id = $estado_emitida_id;
        $factura->saveQuietly();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', [
                'motivo_anulacion' => 'Sin registro de alta',
            ])
            ->assertStatus(500);
    }

    public function test_no_se_puede_anular_dos_veces_la_misma_factura(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', ['motivo_anulacion' => 'Primera anulación'])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', ['motivo_anulacion' => 'Segunda anulación'])
            ->assertStatus(500);
    }

    public function test_no_se_puede_anular_factura_rectificada(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        // Generar rectificativa (marca la original como rectificada)
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Corrección de importes',
            ])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', ['motivo_anulacion' => 'Intento'])
            ->assertStatus(500);
    }

    // -------------------------------------------------------------------------
    // RECTIFICAR
    // -------------------------------------------------------------------------

    public function test_rectificar_factura_emitida_genera_rectificativa_con_registro_de_alta(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Error en importe',
            ])
            ->assertCreated();

        $rectificativa_id = $response->json('data.id');

        $this->assertDatabaseHas('facturas', [
            'id' => $rectificativa_id,
            'factura_rectificada_id' => $factura->id,
        ]);

        $this->assertDatabaseHas('registros_facturacion', ['factura_id' => $rectificativa_id]);
        $this->assertDatabaseHas('registros_evento_facturacion', [
            'factura_id' => $rectificativa_id,
            'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_RECTIFICATIVA_CREADO',
        ]);

        // La original queda marcada como rectificada
        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
            'estado_factura_id' => \App\Models\EstadoFactura::query()->where('codigo', 'rectificada')->value('id'),
        ]);
    }

    public function test_factura_rectificativa_se_puede_marcar_como_pagada(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Abono total',
            ])
            ->assertCreated();

        $rectificativaId = $response->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $rectificativaId . '/marcar-pagada')
            ->assertOk()
            ->assertJsonPath('data.estado_factura', 'pagada')
            ->assertJsonPath('data.pagada', true)
            ->assertJsonPath('data.cobros.0.importe', 121);

        $this->assertDatabaseHas('factura_cobros', [
            'factura_id' => $rectificativaId,
            'importe' => 121,
        ]);
    }

    public function test_no_se_puede_rectificar_factura_en_borrador(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Intento',
            ])
            ->assertStatus(500);
    }

    public function test_no_se_puede_rectificar_factura_anulada(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/anular', ['motivo_anulacion' => 'Anulada primero'])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Intento sobre anulada',
            ])
            ->assertStatus(500);
    }

    public function test_no_se_puede_rectificar_factura_ya_rectificada(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Primera rectificación',
            ])
            ->assertCreated();

        // La original ya está marcada como rectificada; intentamos rectificarla de nuevo
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Segunda rectificación',
            ])
            ->assertStatus(500);
    }

    public function test_no_se_puede_rectificar_factura_rectificativa(): void
    {
        [$user, $cliente] = $this->contexto();
        $factura = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $factura);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/rectificar', [
                'motivo_rectificacion' => 'Rectificación original',
            ])
            ->assertCreated();

        $rectificativa_id = $response->json('data.id');

        // Intentar rectificar la rectificativa
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $rectificativa_id . '/rectificar', [
                'motivo_rectificacion' => 'Rectificar una rectificativa',
            ])
            ->assertStatus(500);
    }

    // -------------------------------------------------------------------------
    // CADENA DE REGISTROS
    // -------------------------------------------------------------------------

    public function test_cadena_de_registros_es_coherente_tras_alta_anulacion_y_rectificacion(): void
    {
        [$user, $cliente] = $this->contexto();

        // Factura 1: emitir y anular
        $f1 = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $f1);
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $f1->id . '/anular', ['motivo_anulacion' => 'Anulación'])
            ->assertOk();

        // Factura 2: emitir y rectificar
        $f2 = $this->crearBorrador($user, $cliente);
        $this->emitir($user, $f2);
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $f2->id . '/rectificar', ['motivo_rectificacion' => 'Rectificación'])
            ->assertCreated();

        // Validar cadena completa
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/empresa/registros-facturacion/validar-cadena')
            ->assertOk()
            ->assertJsonPath('data.valida', true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function crearBorrador(User $user, Cliente $cliente): Factura
    {
        $id = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas', $this->payloadBase($cliente))
            ->assertCreated()
            ->json('data.id');

        return Factura::query()->findOrFail($id);
    }

    private function emitir(User $user, Factura $factura): void
    {
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/empresa/facturas/' . $factura->id . '/emitir')
            ->assertOk();
    }

    private function payloadBase(Cliente $cliente): array
    {
        return [
            'cliente_id' => $cliente->id,
            'tipo_factura_codigo' => 'ordinaria',
            'lineas' => [[
                'descripcion' => 'Servicio de consultoría',
                'cantidad' => 1,
                'precio_unitario' => 100,
                'iva_porcentaje' => 21,
            ]],
        ];
    }

    /**
     * @return array{0: User, 1: Cliente}
     */
    private function contexto(string $sufijo = ''): array
    {
        $sufijo = $sufijo ?: uniqid('', true);

        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'Empresa Test ' . $sufijo,
            'nif' => 'T' . substr(str_pad((string) abs(crc32($sufijo)), 8, '0', STR_PAD_LEFT), 0, 8),
            'direccion_fiscal' => 'Calle Prueba 1',
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        app(ModuloService::class)->activarModulo((int) $empresa->id, 'facturacion');

        $tipo_cliente_id = TipoCliente::query()->where('codigo', 'particular')->value('id')
            ?? TipoCliente::query()->value('id');

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => $tipo_cliente_id,
            'nombre' => 'Cliente Test ' . $sufijo,
            'dni_cif' => 'D' . substr(str_pad((string) abs(crc32('cli' . $sufijo)), 8, '0', STR_PAD_LEFT), 0, 8),
            'activo' => true,
        ]);

        return [$user, $cliente];
    }
}

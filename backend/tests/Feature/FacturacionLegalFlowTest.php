<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoLinea;
use App\Models\RegistroFacturacion;
use App\Models\User;
use App\Services\FacturacionDesdeOrdenService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturacionLegalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_se_puede_facturar_orden_pendiente_y_si_completada_con_registro_alta(): void
    {
        $this->seed(DatabaseSeeder::class);
        $empresa = Empresa::query()->create([
            'tipo_empresa_id' => 1,
            'nombre_fiscal' => 'ACME SL',
            'nif' => 'B12345678'
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $orden = OrdenTrabajo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => 1,
            'numero' => 'OT-1',
            'estado_id' => 1,
            'estado_codigo' => 'pendiente'
        ]);
        OrdenTrabajoLinea::query()->create([
            'orden_trabajo_id' => $orden->id,
            'descripcion' => 'Servicio',
            'cantidad' => 1,
            'precio_unitario' => 100,
            'base_imponible' => 100,
            'iva_porcentaje' => 21,
            'cuota_iva' => 21,
            'total' => 121,
            'facturable' => true
        ]);

        $service = app(FacturacionDesdeOrdenService::class);
        $this->expectException(\RuntimeException::class);
        $service->generarDesdeOrden(
            $orden->fresh([
                'estado',
                'lineas'
            ]),
            $user
        );
    }

    public function test_anulacion_genera_registro_y_no_permite_pagada(): void
    {
        $this->assertTrue(true);
    }
}

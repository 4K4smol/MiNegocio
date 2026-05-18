<?php

namespace Database\Seeders;

use App\Models\OrdenTrabajoEstado;
use Illuminate\Database\Seeder;

class OrdenTrabajoEstadosSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'pendiente', 'nombre' => 'Pendiente', 'descripcion' => 'Orden pendiente de iniciar', 'orden' => 1],
            ['codigo' => 'programada', 'nombre' => 'Programada', 'descripcion' => 'Orden planificada en calendario', 'orden' => 2],
            ['codigo' => 'en_proceso', 'nombre' => 'En proceso', 'descripcion' => 'Orden actualmente en ejecucion', 'orden' => 3],
            ['codigo' => 'completada', 'nombre' => 'Completada', 'descripcion' => 'Orden finalizada correctamente', 'orden' => 4],
            ['codigo' => 'cancelada', 'nombre' => 'Cancelada', 'descripcion' => 'Orden cancelada sin finalizar', 'orden' => 5],
            ['codigo' => 'facturada', 'nombre' => 'Facturada', 'descripcion' => 'Orden ya convertida en factura', 'orden' => 6],
        ];

        foreach ($items as $item) {
            OrdenTrabajoEstado::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TipoFactura;
use Illuminate\Database\Seeder;

class TiposFacturaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'presupuesto', 'nombre' => 'Presupuesto', 'orden' => 1],
            ['codigo' => 'proforma', 'nombre' => 'Proforma', 'orden' => 2],
            ['codigo' => 'ordinaria', 'nombre' => 'Factura ordinaria', 'orden' => 3],
            ['codigo' => 'simplificada', 'nombre' => 'Factura simplificada', 'orden' => 4],
            ['codigo' => 'rectificativa', 'nombre' => 'Factura rectificativa', 'orden' => 5],
            ['codigo' => 'recapitulativa', 'nombre' => 'Factura recapitulativa', 'orden' => 6],
        ];

        foreach ($items as $item) {
            TipoFactura::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TipoFactura;
use Illuminate\Database\Seeder;

class TiposFacturaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'ordinaria', 'nombre' => 'Ordinaria', 'orden' => 1],
            ['codigo' => 'rectificativa', 'nombre' => 'Rectificativa', 'orden' => 2],
        ];

        foreach ($items as $item) {
            TipoFactura::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

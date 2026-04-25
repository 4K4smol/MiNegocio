<?php

namespace Database\Seeders;

use App\Models\TipoInventarioMovimiento;
use Illuminate\Database\Seeder;

class TiposInventarioMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'entrada', 'nombre' => 'Entrada', 'orden' => 1],
            ['codigo' => 'salida', 'nombre' => 'Salida', 'orden' => 2],
            ['codigo' => 'ajuste', 'nombre' => 'Ajuste', 'orden' => 3],
            ['codigo' => 'traslado', 'nombre' => 'Traslado', 'orden' => 4],
        ];

        foreach ($items as $item) {
            TipoInventarioMovimiento::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

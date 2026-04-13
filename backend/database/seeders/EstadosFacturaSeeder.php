<?php

namespace Database\Seeders;

use App\Models\EstadoFactura;
use Illuminate\Database\Seeder;

class EstadosFacturaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'borrador', 'nombre' => 'Borrador', 'es_final' => false, 'permite_edicion' => true, 'orden' => 1],
            ['codigo' => 'emitida', 'nombre' => 'Emitida', 'es_final' => false, 'permite_edicion' => false, 'orden' => 2],
            ['codigo' => 'pagada', 'nombre' => 'Pagada', 'es_final' => true, 'permite_edicion' => false, 'orden' => 3],
            ['codigo' => 'anulada', 'nombre' => 'Anulada', 'es_final' => true, 'permite_edicion' => false, 'orden' => 4],
        ];

        foreach ($items as $item) {
            EstadoFactura::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

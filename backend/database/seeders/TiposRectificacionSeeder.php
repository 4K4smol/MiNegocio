<?php

namespace Database\Seeders;

use App\Models\TipoRectificacion;
use Illuminate\Database\Seeder;

class TiposRectificacionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'por_diferencias', 'nombre' => 'Por diferencias', 'orden' => 1],
            ['codigo' => 'por_sustitucion', 'nombre' => 'Por sustitución', 'orden' => 2],
        ];

        foreach ($items as $item) {
            TipoRectificacion::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

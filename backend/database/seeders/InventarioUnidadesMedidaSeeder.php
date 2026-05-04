<?php

namespace Database\Seeders;

use App\Models\InventarioUnidadMedida;
use Illuminate\Database\Seeder;

class InventarioUnidadesMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'ud', 'nombre' => 'Unidad'],
            ['codigo' => 'kg', 'nombre' => 'Kilogramo'],
            ['codigo' => 'g', 'nombre' => 'Gramo'],
            ['codigo' => 'l', 'nombre' => 'Litro'],
            ['codigo' => 'ml', 'nombre' => 'Mililitro'],
            ['codigo' => 'm', 'nombre' => 'Metro'],
            ['codigo' => 'cm', 'nombre' => 'Centímetro'],
            ['codigo' => 'h', 'nombre' => 'Hora'],
        ];

        foreach ($items as $item) {
            InventarioUnidadMedida::updateOrCreate(['codigo' => $item['codigo']], $item);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TipoRegistroFacturacion;
use Illuminate\Database\Seeder;

class TiposRegistroFacturacionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'alta', 'nombre' => 'Alta', 'orden' => 1],
            ['codigo' => 'anulacion', 'nombre' => 'Anulación', 'orden' => 2],
        ];

        foreach ($items as $item) {
            TipoRegistroFacturacion::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

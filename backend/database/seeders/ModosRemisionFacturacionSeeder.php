<?php

namespace Database\Seeders;

use App\Models\ModoRemisionFacturacion;
use Illuminate\Database\Seeder;

class ModosRemisionFacturacionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'verifactu', 'nombre' => 'VERI*FACTU', 'requiere_firma' => false, 'orden' => 1],
            ['codigo' => 'no_verifactu', 'nombre' => 'No VERI*FACTU', 'requiere_firma' => true, 'orden' => 2],
        ];

        foreach ($items as $item) {
            ModoRemisionFacturacion::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\TipoCliente;
use Illuminate\Database\Seeder;

class TiposClienteSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'particular', 'nombre' => 'Particular', 'orden' => 1],
            ['codigo' => 'empresa', 'nombre' => 'Empresa', 'orden' => 2],
        ];

        foreach ($items as $item) {
            TipoCliente::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

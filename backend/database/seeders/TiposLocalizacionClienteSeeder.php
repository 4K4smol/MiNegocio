<?php

namespace Database\Seeders;

use App\Models\TipoLocalizacionCliente;
use Illuminate\Database\Seeder;

class TiposLocalizacionClienteSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'principal', 'nombre' => 'Principal', 'orden' => 1],
            ['codigo' => 'oficina', 'nombre' => 'Oficina', 'orden' => 2],
            ['codigo' => 'local', 'nombre' => 'Local', 'orden' => 3],
            ['codigo' => 'almacen', 'nombre' => 'Almacén', 'orden' => 4],
            ['codigo' => 'centro_trabajo', 'nombre' => 'Centro de trabajo', 'orden' => 5],
            ['codigo' => 'otro', 'nombre' => 'Otro', 'orden' => 6],
        ];

        foreach ($items as $item) {
            TipoLocalizacionCliente::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

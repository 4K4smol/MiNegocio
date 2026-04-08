<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_empresa')->insert([
            [
                'id' => 1,
                'nombre' => 'autonomo',
                'descripcion' => 'Trabajador autónomo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'sociedad',
                'descripcion' => 'Sociedad mercantil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

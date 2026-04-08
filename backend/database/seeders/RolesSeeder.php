<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'nombre' => 'admin',
                'descripcion' => 'Administrador global del sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'titular',
                'descripcion' => 'Responsable principal de la empresa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'gestor',
                'descripcion' => 'Usuario gestor de la empresa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

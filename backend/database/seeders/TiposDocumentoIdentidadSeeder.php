<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposDocumentoIdentidadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_documento_identidad')->insert([
            [
                'id' => 1,
                'nombre' => 'dni',
                'descripcion' => 'Documento Nacional de Identidad',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'nie',
                'descripcion' => 'Número de Identidad de Extranjero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'pasaporte',
                'descripcion' => 'Pasaporte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosVerificacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados_verificacion')->insert([
            [
                'id' => 1,
                'nombre' => 'pendiente',
                'descripcion' => 'Pendiente de revisión',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'aprobada',
                'descripcion' => 'Verificación aprobada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'rechazada',
                'descripcion' => 'Verificación rechazada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

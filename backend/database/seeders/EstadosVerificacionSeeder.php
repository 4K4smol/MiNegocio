<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosVerificacionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nombre' => 'pendiente', 'descripcion' => 'Pendiente de revision'],
            ['nombre' => 'en_revision', 'descripcion' => 'Verificacion en proceso de revision'],
            ['nombre' => 'aprobada', 'descripcion' => 'Verificacion aprobada'],
            ['nombre' => 'rechazada', 'descripcion' => 'Verificacion rechazada'],
            ['nombre' => 'subsanacion', 'descripcion' => 'Pendiente de subsanacion documental'],
        ] as $estado) {
            DB::table('estados_verificacion')->updateOrInsert(
                ['nombre' => $estado['nombre']],
                [
                    'descripcion' => $estado['descripcion'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}

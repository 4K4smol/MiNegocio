<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EstadoVerificacion;
use Illuminate\Database\Seeder;

class EstadosVerificacionSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            [
                'nombre' => 'pendiente',
                'descripcion' => 'Pendiente de revisión',
            ],
            [
                'nombre' => 'en_revision',
                'descripcion' => 'Verificación en proceso de revisión',
            ],
            [
                'nombre' => 'aprobada',
                'descripcion' => 'Verificación aprobada',
            ],
            [
                'nombre' => 'rechazada',
                'descripcion' => 'Verificación rechazada',
            ],
        ];

        foreach ($estados as $estado) {
            EstadoVerificacion::query()->updateOrCreate(
                ['nombre' => $estado['nombre']],
                ['descripcion' => $estado['descripcion']],
            );
        }
    }
}

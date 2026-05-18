<?php

namespace Database\Seeders;

use App\Models\EstadoRemisionFacturacion;
use Illuminate\Database\Seeder;

class EstadosRemisionFacturacionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'no_aplica', 'nombre' => 'No aplica', 'orden' => 1],
            ['codigo' => 'pendiente', 'nombre' => 'Pendiente', 'orden' => 2],
            ['codigo' => 'enviado', 'nombre' => 'Enviado', 'orden' => 3],
            ['codigo' => 'aceptado', 'nombre' => 'Aceptado', 'orden' => 4],
            ['codigo' => 'rechazado', 'nombre' => 'Rechazado', 'orden' => 5],
            ['codigo' => 'error', 'nombre' => 'Error', 'orden' => 6],
            ['codigo' => 'reintentando', 'nombre' => 'Reintentando', 'orden' => 7],
        ];

        foreach ($items as $item) {
            EstadoRemisionFacturacion::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

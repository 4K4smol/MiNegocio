<?php

namespace Database\Seeders;

use App\Models\OrdenTrabajoPrioridad;
use Illuminate\Database\Seeder;

class OrdenTrabajoPrioridadesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'baja', 'nombre' => 'Baja', 'descripcion' => 'Prioridad baja', 'activo' => true, 'orden' => 1],
            ['codigo' => 'normal', 'nombre' => 'Normal', 'descripcion' => 'Prioridad normal', 'activo' => true, 'orden' => 2],
            ['codigo' => 'alta', 'nombre' => 'Alta', 'descripcion' => 'Prioridad alta', 'activo' => true, 'orden' => 3],
            ['codigo' => 'urgente', 'nombre' => 'Urgente', 'descripcion' => 'Prioridad urgente', 'activo' => true, 'orden' => 4],
        ];

        foreach ($items as $item) {
            OrdenTrabajoPrioridad::updateOrCreate(['codigo' => $item['codigo']], $item);
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TipoTarifaServicio;
use Illuminate\Database\Seeder;

class TipoTarifaServicioSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['codigo' => 'estandar', 'nombre' => 'Estandar', 'descripcion' => 'Tarifa habitual del servicio', 'activo' => true, 'orden' => 1, 'es_sistema' => true],
            ['codigo' => 'urgente', 'nombre' => 'Urgente', 'descripcion' => 'Precio para trabajos urgentes', 'activo' => true, 'orden' => 2, 'es_sistema' => true],
            ['codigo' => 'especial', 'nombre' => 'Especial', 'descripcion' => 'Precio pactado o especial', 'activo' => true, 'orden' => 3, 'es_sistema' => true],
            ['codigo' => 'fin_semana', 'nombre' => 'Fin de semana', 'descripcion' => 'Precio para trabajos en fin de semana', 'activo' => true, 'orden' => 4, 'es_sistema' => true],
            ['codigo' => 'festivo', 'nombre' => 'Festivo', 'descripcion' => 'Precio para trabajos en festivo', 'activo' => true, 'orden' => 5, 'es_sistema' => true],
            ['codigo' => 'nocturno', 'nombre' => 'Nocturno', 'descripcion' => 'Precio para trabajos nocturnos', 'activo' => true, 'orden' => 6, 'es_sistema' => true],
        ];

        foreach ($tipos as $tipo) {
            TipoTarifaServicio::query()->updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo,
            );
        }
    }
}

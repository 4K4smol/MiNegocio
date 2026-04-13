<?php

namespace Database\Seeders;

use App\Models\TipoEventoFacturacion;
use Illuminate\Database\Seeder;

class TiposEventoFacturacionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'inicio_no_verifactu', 'nombre' => 'Inicio no VERI*FACTU', 'orden' => 1],
            ['codigo' => 'fin_no_verifactu', 'nombre' => 'Fin no VERI*FACTU', 'orden' => 2],
            ['codigo' => 'deteccion_anomalias_lanzada', 'nombre' => 'Lanzamiento detección anomalías', 'orden' => 3],
            ['codigo' => 'anomalia_integridad', 'nombre' => 'Anomalía integridad', 'orden' => 4],
            ['codigo' => 'anomalia_inalterabilidad', 'nombre' => 'Anomalía inalterabilidad', 'orden' => 5],
            ['codigo' => 'anomalia_trazabilidad', 'nombre' => 'Anomalía trazabilidad', 'orden' => 6],
        ];

        foreach ($items as $item) {
            TipoEventoFacturacion::updateOrCreate(['codigo' => $item['codigo']], $item + ['activo' => true]);
        }
    }
}

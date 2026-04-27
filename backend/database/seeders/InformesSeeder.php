<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Informe;
use App\Models\User;
use Illuminate\Database\Seeder;

class InformesSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();

        if (!$empresa) {
            $this->command?->warn('No hay empresas para crear informes.');
            return;
        }

        $usuario = User::query()->where('empresa_id', $empresa->id)->first();

        Informe::query()->updateOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'REPORTE_VENTAS_MENSUAL'],
            [
                'generado_por' => $usuario?->id,
                'nombre' => 'Reporte mensual de ventas',
                'tipo' => 'VENTAS',
                'formato' => 'JSON',
                'estado_codigo' => 'COMPLETADO',
                'filtros' => ['periodo' => now()->format('Y-m')],
                'resumen' => ['facturas' => 0, 'total' => 0],
                'contenido' => json_encode(['detalle' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'generado_en' => now(),
            ],
        );
    }
}

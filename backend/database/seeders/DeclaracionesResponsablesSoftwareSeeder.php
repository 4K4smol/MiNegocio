<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeclaracionResponsableSoftware;
use App\Services\DeclaracionResponsableSoftwareService;
use Illuminate\Database\Seeder;

class DeclaracionesResponsablesSoftwareSeeder extends Seeder
{
    public function run(): void
    {
        if (DeclaracionResponsableSoftware::query()
            ->where('codigo_software', 'MINEGOCIO-SIF')
            ->where('version_software', '1.0.0')
            ->exists()) {
            return;
        }

        app(DeclaracionResponsableSoftwareService::class)->crear([
            'nombre_software' => 'MiNegocio',
            'codigo_software' => 'MINEGOCIO-SIF',
            'version_software' => '1.0.0',
            'productor_nombre' => 'MiNegocio',
            'modo_verifactu' => false,
            'permite_multiples_obligados' => true,
            'descripcion' => 'Sistema SaaS/CRM con modulo de facturacion y adaptacion conceptual VeriFactu/RRSIF.',
            'componentes' => [
                'facturacion',
                'registros_facturacion',
                'cadena_hash',
                'eventos_facturacion',
                'remision_verifactu_simulada',
            ],
            'metadatos' => [
                'origen' => 'seeder',
                'tipo' => 'declaracion_inicial',
            ],
        ]);
    }
}

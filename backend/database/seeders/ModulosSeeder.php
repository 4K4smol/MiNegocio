<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            [
                'codigo' => 'facturacion',
                'nombre' => 'Facturacion',
                'descripcion' => 'Gestion de facturas, registros de facturacion y VeriFactu simulado.',
                'activo' => true,
                'orden_visual' => 1,
                'icono' => 'receipt',
            ],
            [
                'codigo' => 'clientes',
                'nombre' => 'Clientes',
                'descripcion' => 'Gestion de clientes y localizaciones asociadas.',
                'activo' => true,
                'orden_visual' => 2,
                'icono' => 'users',
            ],
            [
                'codigo' => 'servicios',
                'nombre' => 'Servicios',
                'descripcion' => 'Catalogo de servicios, tarifas y precios.',
                'activo' => true,
                'orden_visual' => 3,
                'icono' => 'briefcase',
            ],
            [
                'codigo' => 'ordenes',
                'nombre' => 'Ordenes de trabajo',
                'descripcion' => 'Gestion operativa de ordenes de trabajo.',
                'activo' => true,
                'orden_visual' => 4,
                'icono' => 'clipboard-list',
            ],
            [
                'codigo' => 'calendario',
                'nombre' => 'Calendario',
                'descripcion' => 'Planificacion de eventos y ordenes programadas.',
                'activo' => true,
                'orden_visual' => 5,
                'icono' => 'calendar',
            ],
            [
                'codigo' => 'inventario',
                'nombre' => 'Inventario',
                'descripcion' => 'Control de productos, categorias, ubicaciones y movimientos.',
                'activo' => true,
                'orden_visual' => 6,
                'icono' => 'boxes',
            ],
            [
                'codigo' => 'informes',
                'nombre' => 'Informes',
                'descripcion' => 'Informes operativos por empresa.',
                'activo' => true,
                'orden_visual' => 7,
                'icono' => 'bar-chart',
            ],
            [
                'codigo' => 'paginas_web',
                'nombre' => 'Paginas web',
                'descripcion' => 'Gestion futura de paginas web y contactos.',
                'activo' => true,
                'orden_visual' => 8,
                'icono' => 'globe',
            ],
        ];

        foreach ($modulos as $modulo) {
            Modulo::query()->updateOrCreate(
                ['codigo' => $modulo['codigo']],
                $modulo
            );
        }
    }
}

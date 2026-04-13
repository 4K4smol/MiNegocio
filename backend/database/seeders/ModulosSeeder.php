<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            [
                'codigo' => 'FACTURACION',
                'nombre' => 'Facturación',
                'descripcion' => 'Gestión de facturas emitidas, rectificativas y estados.',
                'activo' => true,
                'orden_visual' => 1,
                'icono' => 'receipt',
            ],
            [
                'codigo' => 'CLIENTES',
                'nombre' => 'Clientes',
                'descripcion' => 'Gestión de clientes y localizaciones asociadas.',
                'activo' => true,
                'orden_visual' => 2,
                'icono' => 'users',
            ],
            [
                'codigo' => 'INVENTARIO',
                'nombre' => 'Inventario',
                'descripcion' => 'Control de productos, categorías, ubicaciones y movimientos.',
                'activo' => true,
                'orden_visual' => 3,
                'icono' => 'boxes',
            ],
            [
                'codigo' => 'REPORTES',
                'nombre' => 'Reportes',
                'descripcion' => 'Paneles y reportes operativos por empresa.',
                'activo' => true,
                'orden_visual' => 4,
                'icono' => 'bar-chart',
            ],
            [
                'codigo' => 'USUARIOS',
                'nombre' => 'Usuarios y roles',
                'descripcion' => 'Administración de usuarios, roles y permisos.',
                'activo' => true,
                'orden_visual' => 5,
                'icono' => 'shield',
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

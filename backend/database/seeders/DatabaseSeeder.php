<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            TiposDocumentoIdentidadSeeder::class,
            TiposEmpresaSeeder::class,
            EmpresaSeeder::class,
            ModulosSeeder::class,

            EstadosFacturaSeeder::class,
            EstadosRemisionFacturacionSeeder::class,
            EstadosVerificacionSeeder::class,
            ModosRemisionFacturacionSeeder::class,
            TiposEventoFacturacionSeeder::class,
            TiposFacturaSeeder::class,
            TiposRectificacionSeeder::class,
            TiposRegistroFacturacionSeeder::class,
            TiposClienteSeeder::class,
            TiposInventarioMovimientoSeeder::class,
            InventarioUnidadesMedidaSeeder::class,
            OrdenTrabajoEstadosSeeder::class,
            OrdenTrabajoPrioridadesSeeder::class,

            ClienteSeeder::class,

            PaginasWebSeeder::class,
            PaginasWebContactosSeeder::class,
            CalendarioEventosSeeder::class,
            TipoTarifaServicioSeeder::class,
        ]);
    }
}

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
            TiposDocumentoIdentidadSeeder::class,
            TiposEmpresaSeeder::class,
            EmpresaSeeder::class,
            ModulosSeeder::class,

            EstadosFacturaSeeder::class,
            EstadosVerificacionSeeder::class,
            ModosRemisionFacturacionSeeder::class,
            TiposEventoFacturacionSeeder::class,
            TiposFacturaSeeder::class,
            TiposRectificacionSeeder::class,
            TiposRegistroFacturacionSeeder::class,
            TiposClienteSeeder::class,
            TiposLocalizacionClienteSeeder::class,
            TiposInventarioMovimientoSeeder::class,

            ClienteSeeder::class,
            LocalizacionClienteSeeder::class,

            PaginasWebSeeder::class,
            PaginasWebContactosSeeder::class,
            InformesSeeder::class,
            CalendarioEventosSeeder::class,
        ]);
    }
}

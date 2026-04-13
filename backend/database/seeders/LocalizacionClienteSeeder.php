<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\LocalizacionCliente;
use Illuminate\Database\Seeder;

class LocalizacionClienteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('es_ES');

        $clientes = Cliente::all();

        if ($clientes->isEmpty()) {
            $this->command?->warn('No hay clientes. Ejecuta primero ClienteSeeder.');
            return;
        }

        foreach ($clientes as $cliente) {
            LocalizacionCliente::updateOrCreate(
                [
                    'cliente_id' => $cliente->id,
                    'nombre_localizacion' => 'Principal',
                ],
                [
                    'tipo_localizacion' => 'principal',
                    'direccion' => $faker->streetAddress(),
                    'direccion_linea_2' => $faker->optional()->secondaryAddress(),
                    'ciudad' => $faker->city(),
                    'provincia' => $faker->state(),
                    'codigo_postal' => $faker->postcode(),
                    'pais' => 'España',
                    'observaciones' => 'Localización principal del cliente',
                    'es_principal' => true,
                    'activo' => true,
                ]
            );

            if ($faker->boolean(40)) {
                LocalizacionCliente::updateOrCreate(
                    [
                        'cliente_id' => $cliente->id,
                        'nombre_localizacion' => 'Centro de trabajo',
                    ],
                    [
                        'tipo_localizacion' => 'centro_trabajo',
                        'direccion' => $faker->streetAddress(),
                        'direccion_linea_2' => $faker->optional()->secondaryAddress(),
                        'ciudad' => $faker->city(),
                        'provincia' => $faker->state(),
                        'codigo_postal' => $faker->postcode(),
                        'pais' => 'España',
                        'observaciones' => 'Ubicación secundaria para trabajos o servicios',
                        'es_principal' => false,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}

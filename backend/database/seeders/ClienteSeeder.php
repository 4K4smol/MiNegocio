<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\LocalizacionCliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cliente1 = Cliente::create([
            'tipo_cliente' => 'empresa',
            'nombre' => 'Limpiezas Norte',
            'razon_social' => 'Limpiezas Norte S.L.',
            'dni_cif' => 'B12345678',
            'telefono' => '942000111',
            'email' => 'contacto@limpiezasnorte.es',
            'persona_contacto' => 'María Gómez',
            'notas' => 'Cliente con varios centros de trabajo.',
            'activo' => true,
        ]);

        LocalizacionCliente::create([
            'cliente_id' => $cliente1->id,
            'nombre_localizacion' => 'Oficina central',
            'tipo_localizacion' => 'principal',
            'direccion' => 'Calle Mayor 10',
            'ciudad' => 'Torrelavega',
            'provincia' => 'Cantabria',
            'codigo_postal' => '39300',
            'pais' => 'España',
            'es_principal' => true,
            'activo' => true,
        ]);

        LocalizacionCliente::create([
            'cliente_id' => $cliente1->id,
            'nombre_localizacion' => 'Local Santander',
            'tipo_localizacion' => 'local',
            'direccion' => 'Avenida Reina Victoria 25',
            'ciudad' => 'Santander',
            'provincia' => 'Cantabria',
            'codigo_postal' => '39004',
            'pais' => 'España',
            'es_principal' => false,
            'activo' => true,
        ]);

        $cliente2 = Cliente::create([
            'tipo_cliente' => 'particular',
            'nombre' => 'Juan',
            'apellidos' => 'Pérez López',
            'dni_cif' => '12345678Z',
            'telefono' => '600123456',
            'email' => 'juanperez@email.com',
            'persona_contacto' => null,
            'notas' => 'Cliente particular.',
            'activo' => true,
        ]);

        LocalizacionCliente::create([
            'cliente_id' => $cliente2->id,
            'nombre_localizacion' => 'Vivienda habitual',
            'tipo_localizacion' => 'principal',
            'direccion' => 'Barrio Ejemplo 14',
            'ciudad' => 'Reocín',
            'provincia' => 'Cantabria',
            'codigo_postal' => '39538',
            'pais' => 'España',
            'es_principal' => true,
            'activo' => true,
        ]);
    }
}

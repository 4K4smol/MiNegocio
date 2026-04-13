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
            'empresa_id' => 1
        ]);
    }
}

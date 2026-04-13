<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\TipoEmpresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tipoEmpresa = TipoEmpresa::query()->first();

        if (!$tipoEmpresa) {
            $this->command?->warn('No hay tipos de empresa. Ejecuta antes TiposEmpresaSeeder.');
            return;
        }

        $empresas = [
            [
                'tipo_empresa_id'   => $tipoEmpresa->id,
                'nombre_fiscal'     => 'Limpiezas Duo S.L.',
                'nombre_comercial'  => 'Limpiezas Duo',
                'nif'               => 'B12345678',
                'correo'            => 'info@limpiezasduo.es',
                'telefono'          => '600123123',
                'direccion_fiscal'  => 'Calle Principal 12',
                'codigo_postal'     => '39300',
                'municipio'         => 'Torrelavega',
                'provincia'         => 'Cantabria',
                'pais'              => 'España',
                'activa'            => true,
            ],
            [
                'tipo_empresa_id'   => $tipoEmpresa->id,
                'nombre_fiscal'     => 'Servicios Integrales del Norte S.L.',
                'nombre_comercial'  => 'SINorte',
                'nif'               => 'B87654321',
                'correo'            => 'contacto@sinorte.es',
                'telefono'          => '611222333',
                'direccion_fiscal'  => 'Avenida de España 45',
                'codigo_postal'     => '39001',
                'municipio'         => 'Santander',
                'provincia'         => 'Cantabria',
                'pais'              => 'España',
                'activa'            => true,
            ],
        ];

        foreach ($empresas as $empresa) {
            Empresa::updateOrCreate(
                ['nif' => $empresa['nif']],
                $empresa
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\PaginaWeb;
use Illuminate\Database\Seeder;

class PaginasWebSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();

        if (!$empresa) {
            $this->command?->warn('No hay empresas para crear páginas web.');
            return;
        }

        $paginas = [
            [
                'slug' => 'inicio',
                'titulo' => 'Inicio',
                'plantilla' => 'base',
                'estado_codigo' => 'PUBLICADA',
                'publicada' => true,
                'publicada_en' => now(),
                'resumen' => 'Página principal corporativa.',
                'contenido' => '<h1>Bienvenido a nuestro negocio</h1>',
                'configuracion' => ['mostrar_hero' => true],
            ],
            [
                'slug' => 'servicios',
                'titulo' => 'Servicios',
                'plantilla' => 'base',
                'estado_codigo' => 'BORRADOR',
                'publicada' => false,
                'publicada_en' => null,
                'resumen' => 'Listado de servicios principales.',
                'contenido' => '<h2>Servicios destacados</h2>',
                'configuracion' => ['mostrar_tarifas' => false],
            ],
        ];

        foreach ($paginas as $pagina) {
            PaginaWeb::query()->updateOrCreate(
                ['empresa_id' => $empresa->id, 'slug' => $pagina['slug']],
                $pagina + ['empresa_id' => $empresa->id],
            );
        }
    }
}

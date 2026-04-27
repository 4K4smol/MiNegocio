<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\PaginaWeb;
use App\Models\PaginaWebContacto;
use Illuminate\Database\Seeder;

class PaginasWebContactosSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();

        if (!$empresa) {
            $this->command?->warn('No hay empresas para crear contactos web.');
            return;
        }

        $pagina = PaginaWeb::query()->where('empresa_id', $empresa->id)->where('slug', 'inicio')->first();

        PaginaWebContacto::query()->updateOrCreate(
            [
                'empresa_id' => $empresa->id,
                'email' => 'cliente@ejemplo.com',
                'mensaje' => 'Quiero solicitar un presupuesto para mantenimiento mensual.',
            ],
            [
                'pagina_web_id' => $pagina?->id,
                'nombre' => 'Laura Pérez',
                'telefono' => '600700800',
                'asunto' => 'Solicitud de presupuesto',
                'tratado' => false,
                'meta' => ['origen' => 'seed'],
            ],
        );
    }
}

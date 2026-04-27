<?php

namespace Database\Seeders;

use App\Models\CalendarioEvento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarioEventosSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->first();

        if (!$empresa) {
            $this->command?->warn('No hay empresas para crear eventos de calendario.');
            return;
        }

        $cliente = Cliente::query()->where('empresa_id', $empresa->id)->first();
        $ordenTrabajo = OrdenTrabajo::query()->where('empresa_id', $empresa->id)->first();
        $usuario = User::query()->where('empresa_id', $empresa->id)->first();

        CalendarioEvento::query()->updateOrCreate(
            [
                'empresa_id' => $empresa->id,
                'titulo' => 'Visita técnica inicial',
                'inicio' => now()->addDay()->startOfHour(),
            ],
            [
                'cliente_id' => $cliente?->id,
                'orden_trabajo_id' => $ordenTrabajo?->id,
                'creado_por' => $usuario?->id,
                'descripcion' => 'Revisión general de instalaciones y toma de requisitos.',
                'tipo' => 'VISITA',
                'fin' => now()->addDay()->startOfHour()->addHour(),
                'todo_el_dia' => false,
                'estado_codigo' => 'PROGRAMADO',
                'ubicacion' => 'Instalaciones del cliente',
                'meta' => ['recordatorio_minutos' => 30],
            ],
        );
    }
}

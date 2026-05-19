<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarioEvento;
use App\Models\Cliente;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CalendarioEventoService
{
    private const ROL_ADMIN = 'admin';

    public function crearEvento(array $data, User $user): CalendarioEvento
    {
        return DB::transaction(function () use ($data, $user): CalendarioEvento {
            $empresaId = $this->resolverEmpresaId($data, $user);

            $evento = CalendarioEvento::query()->create([
                'empresa_id' => $empresaId,
                'cliente_id' => $data['cliente_id'] ?? null,
                'orden_trabajo_id' => $data['orden_trabajo_id'] ?? null,
                'creado_por' => $user->id,
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'] ?? null,
                'tipo' => $data['tipo'],
                'inicio' => $data['inicio'],
                'fin' => $data['fin'] ?? null,
                'todo_el_dia' => (bool) ($data['todo_el_dia'] ?? false),
                'estado_codigo' => $data['estado_codigo'] ?? 'PROGRAMADO',
                'ubicacion' => $data['ubicacion'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            return $evento->fresh(['cliente', 'ordenTrabajo', 'creador']);
        });
    }

    public function actualizarEvento(CalendarioEvento $evento, array $data, User $user): CalendarioEvento
    {
        return DB::transaction(function () use ($evento, $data, $user): CalendarioEvento {
            $this->validarAccesoEmpresa($evento, $user);

            $evento->fill(collect($data)->only([
                'cliente_id',
                'orden_trabajo_id',
                'titulo',
                'descripcion',
                'tipo',
                'inicio',
                'fin',
                'todo_el_dia',
                'estado_codigo',
                'ubicacion',
                'meta',
            ])->toArray());

            $evento->save();

            return $evento->fresh(['cliente', 'ordenTrabajo', 'creador']);
        });
    }

    public function crearOActualizarDesdeOrden(OrdenTrabajo $orden, User $user): ?CalendarioEvento
    {
        if ($orden->fecha_programada_inicio === null) {
            return null;
        }

        return DB::transaction(function () use ($orden, $user): CalendarioEvento {
            $orden->loadMissing(['cliente', 'estado']);
            $evento = CalendarioEvento::query()
                ->where('empresa_id', $orden->empresa_id)
                ->where('orden_trabajo_id', $orden->id)
                ->lockForUpdate()
                ->first();

            $data = [
                'empresa_id' => $orden->empresa_id,
                'cliente_id' => $orden->cliente_id,
                'orden_trabajo_id' => $orden->id,
                'creado_por' => $evento?->creado_por ?? $user->id,
                'titulo' => 'Orden '.$orden->numero,
                'descripcion' => $orden->notas_cliente ?: $orden->notas_internas,
                'tipo' => 'ORDEN_TRABAJO',
                'inicio' => $orden->fecha_programada_inicio,
                'fin' => $orden->fecha_programada_fin ?: $orden->fecha_programada_inicio,
                'todo_el_dia' => false,
                'estado_codigo' => $orden->estado?->codigo ?? $orden->estado_codigo,
                'ubicacion' => $orden->cliente
                    ? (implode(', ', array_filter([$orden->cliente->direccion, $orden->cliente->ciudad, $orden->cliente->provincia])) ?: null)
                    : null,
                'meta' => [
                    'origen' => 'orden_trabajo',
                    'numero_orden' => $orden->numero,
                    'tecnico_responsable_id' => $orden->tecnico_responsable_id,
                ],
            ];

            if ($evento) {
                $evento->fill($data);
                $evento->save();

                return $evento->fresh(['cliente', 'ordenTrabajo', 'creador']);
            }

            return CalendarioEvento::query()
                ->create($data)
                ->fresh(['cliente', 'ordenTrabajo', 'creador']);
        });
    }

    public function marcarDesdeOrden(OrdenTrabajo $orden, string $estadoCodigo): void
    {
        CalendarioEvento::query()
            ->where('empresa_id', $orden->empresa_id)
            ->where('orden_trabajo_id', $orden->id)
            ->update(['estado_codigo' => $estadoCodigo]);
    }

    private function resolverEmpresaId(array $data, User $user): int
    {
        if (strtolower((string) $user->role?->nombre) !== self::ROL_ADMIN) {
            return (int) $user->empresa_id;
        }

        if (isset($data['orden_trabajo_id'])) {
            return (int) OrdenTrabajo::query()->whereKey($data['orden_trabajo_id'])->value('empresa_id');
        }

        if (isset($data['cliente_id'])) {
            return (int) Cliente::query()->whereKey($data['cliente_id'])->value('empresa_id');
        }

        if ($user->empresa_id !== null) {
            return (int) $user->empresa_id;
        }

        throw new RuntimeException('No se pudo resolver la empresa del evento de calendario.');
    }

    private function validarAccesoEmpresa(CalendarioEvento $evento, User $user): void
    {
        if (strtolower((string) $user->role?->nombre) === self::ROL_ADMIN) {
            return;
        }

        if ((int) $evento->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes modificar eventos de otra empresa.');
        }
    }
}

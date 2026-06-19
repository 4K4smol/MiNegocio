<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarioEvento;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\OrdenTrabajo;
use App\Models\RegistroFacturacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardService
{
    private const ROL_ADMIN = 'admin';

    public function resumen(User $user): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        return [
            'clientes_activos' => $this->aplicarEmpresa(Cliente::query(), $user)->where('activo', true)->count(),
            'ordenes_pendientes' => $this->aplicarEmpresa(OrdenTrabajo::query(), $user)->whereNotIn('estado_codigo', ['completada', 'cancelada', 'facturada'])->count(),
            'ordenes_completadas_mes' => $this->aplicarEmpresa(OrdenTrabajo::query(), $user)
                ->where('estado_codigo', 'completada')
                ->whereBetween('fecha_cierre', [$inicioMes, $finMes])
                ->count(),
            'facturas_emitidas_mes' => $this->aplicarEmpresa(Factura::query(), $user)
                ->whereBetween('fecha_emision', [$inicioMes->toDateString(), $finMes->toDateString()])
                ->count(),
            'total_facturado_mes' => round((float) $this->aplicarEmpresa(Factura::query(), $user)
                ->whereBetween('fecha_emision', [$inicioMes->toDateString(), $finMes->toDateString()])
                ->sum('total'), 2),
            'registros_facturacion_pendientes' => $this->aplicarEmpresa(RegistroFacturacion::query(), $user)
                ->where(function (Builder $query): void {
                    $query->where('estado_remision', 'pendiente')->orWhereNull('estado_remision');
                })
                ->count(),
        ];
    }

    public function proximasOrdenes(User $user, int $limite = 10): Collection
    {
        return $this->aplicarEmpresa(OrdenTrabajo::query(), $user)
            ->with(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio'])
            ->whereNotIn('estado_codigo', ['completada', 'cancelada', 'facturada'])
            ->whereNotNull('fecha_programada_inicio')
            ->orderByRaw('CASE WHEN fecha_programada_inicio < ? THEN 0 ELSE 1 END', [now()])
            ->orderBy('fecha_programada_inicio')
            ->limit(max(1, min($limite, 50)))
            ->get();
    }

    public function calendario(User $user, ?string $desde = null, ?string $hasta = null): Collection
    {
        $query = $this->aplicarEmpresa(CalendarioEvento::query(), $user)
            ->with(['cliente', 'ordenTrabajo', 'creador'])
            ->orderBy('inicio');

        if ($desde !== null) {
            $query->where('inicio', '>=', $desde);
        }

        if ($hasta !== null) {
            $query->where(function (Builder $query) use ($hasta): void {
                $query->where('inicio', '<=', $hasta)
                    ->orWhere('fin', '<=', $hasta);
            });
        }

        return $query->get();
    }

    private function aplicarEmpresa(Builder $query, User $user): Builder
    {
        if (strtolower((string) $user->role?->nombre) === self::ROL_ADMIN) {
            return $query;
        }

        return $query->where('empresa_id', $user->empresa_id);
    }
}

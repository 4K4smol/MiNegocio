<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenTrabajoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = (float) ($this->subtotal ?? $this->lineas->sum('base_imponible'));
        $iva = (float) ($this->iva_total ?? $this->lineas->sum('cuota_iva'));
        $total = (float) ($this->total ?? ($subtotal + $iva));
        $lineasFacturablesPendientes = $this->lineas->filter(
            fn ($linea) => $linea->facturable && (float) $linea->cantidad > (float) $linea->facturado_cantidad
        );
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'cliente' => ClienteResource::make($this->whenLoaded('cliente')),
            'localizacion_cliente_id' => $this->localizacion_cliente_id,
            'localizacion' => $this->whenLoaded('localizacionCliente', fn () => $this->localizacionCliente ? [
                'id' => $this->localizacionCliente->id,
                'nombre' => $this->localizacionCliente->nombre_localizacion,
                'direccion' => $this->localizacionCliente->direccion,
                'direccion_completa' => $this->localizacionCliente->direccion_completa,
                'ciudad' => $this->localizacionCliente->ciudad,
                'provincia' => $this->localizacionCliente->provincia,
                'codigo_postal' => $this->localizacionCliente->codigo_postal,
                'pais' => $this->localizacionCliente->pais,
            ] : null),
            'numero' => $this->numero,
            'estado' => $this->estado?->codigo,
            'prioridad' => $this->prioridad?->codigo,
            'fechas' => [
                'apertura' => $this->fecha_apertura,
                'programada_inicio' => $this->fecha_programada_inicio,
                'programada_fin' => $this->fecha_programada_fin,
                'inicio_real' => $this->fecha_inicio_real,
                'fin_real' => $this->fecha_fin_real,
                'cierre' => $this->fecha_cierre,
            ],
            'tecnico_responsable' => $this->tecnicoResponsable?->only(['id', 'name', 'email']),
            'notas' => ['cliente' => $this->notas_cliente, 'internas' => $this->notas_internas],
            'lineas' => OrdenTrabajoLineaResource::collection($this->whenLoaded('lineas')),
            'eventos_calendario' => CalendarioEventoResource::collection($this->whenLoaded('eventosCalendario')),
            'totales' => [
                'subtotal' => round($subtotal, 2),
                'iva_total' => round($iva, 2),
                'cuota_iva' => round($iva, 2),
                'total' => round($total, 2),
            ],
            'puede_facturarse' => $this->estado?->codigo === 'completada' && $lineasFacturablesPendientes->isNotEmpty(),
        ];
    }
}

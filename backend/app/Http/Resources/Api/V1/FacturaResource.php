<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Services\RegistroFacturacionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'cliente_id' => $this->cliente_id,
            'orden_trabajo_id' => $this->orden_trabajo_id,
            'tipo_factura' => $this->tipoFactura?->codigo,
            'estado_factura' => $this->estadoFactura?->codigo,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'numero_completo' => $this->numero_completo,
            'fecha_emision' => $this->fecha_emision,
            'fecha_operacion' => $this->fecha_operacion,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'factura_rectificada_id' => $this->factura_rectificada_id,
            'motivo_rectificacion' => $this->motivo_rectificacion,
            'emisor_nif' => $this->emisor_nif,
            'emisor_nombre_razon_social' => $this->emisor_nombre_razon_social,
            'receptor_nif' => $this->receptor_nif,
            'receptor_nombre_razon_social' => $this->receptor_nombre_razon_social,
            'receptor_domicilio_fiscal' => $this->receptor_domicilio_fiscal,
            'subtotal' => (float) $this->subtotal,
            'cuota_iva' => (float) $this->cuota_iva,
            'base_imponible' => (float) ($this->base_imponible ?: $this->subtotal),
            'total_iva' => (float) ($this->total_iva ?: $this->cuota_iva),
            'total_retencion' => (float) $this->total_retencion,
            'total_descuento' => (float) $this->total_descuento,
            'total' => (float) $this->total,
            'observaciones' => $this->observaciones,
            'metadatos' => $this->metadatos,
            'pagada' => (bool) $this->pagada,
            'fecha_pago' => $this->fecha_pago,
            'ultimo_registro_facturacion_hash' => $this->whenLoaded('registrosFacturacion', fn () => $this->registrosFacturacion->sortByDesc('id')->first()?->hash_actual),
            'estado_remision_aeat' => $this->whenLoaded('registrosFacturacion', fn () => $this->registrosFacturacion->sortByDesc('id')->first()?->estado_remision ?? 'pendiente'),
            'enviado_aeat_at' => $this->whenLoaded('registrosFacturacion', fn () => $this->registrosFacturacion->sortByDesc('id')->first()?->enviado_aeat_at),
            'datos_qr' => $this->whenLoaded('registrosFacturacion', fn () => app(RegistroFacturacionService::class)->generarPayloadQrInterno($this->resource, $this->registrosFacturacion->sortByDesc('id')->first())),
            'lineas' => FacturaLineaResource::collection($this->whenLoaded('lineas')),
            'impuestos' => FacturaImpuestoResource::collection($this->whenLoaded('impuestos')),
            'cobros' => FacturaCobroResource::collection($this->whenLoaded('cobros')),
            'historial' => FacturaHistorialResource::collection($this->whenLoaded('historial')),
            'registros_facturacion' => RegistroFacturacionResource::collection($this->whenLoaded('registrosFacturacion')),
        ];
    }
}

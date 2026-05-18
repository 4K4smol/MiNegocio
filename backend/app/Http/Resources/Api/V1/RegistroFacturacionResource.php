<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroFacturacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresa_id,
            'factura_id' => $this->factura_id,
            'tipo_registro' => $this->tipoRegistroFacturacion?->codigo,
            'modo_remision' => $this->modoRemisionFacturacion?->codigo,
            'estado_remision' => $this->estadoRemisionFacturacion?->codigo ?? $this->estado_remision,
            'registro_anterior_id' => $this->registro_anterior_id,
            'registro_anulado_id' => $this->registro_anulado_id,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'numero_completo' => $this->numero_completo,
            'fecha_expedicion' => $this->fecha_expedicion,
            'hash_anterior' => $this->registro_anterior_hash_64,
            'hash_actual' => $this->hash_actual,
            'payload_json' => $this->payload_json,
            'qr_contenido' => $this->qr_contenido,
            'generado_at' => $this->generado_at,
            'remitido_at' => $this->remitido_at ?? $this->enviado_aeat_at,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudRegistroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'empresa_id' => $this->empresa_id,
            'nombre_fiscal' => $this->empresa?->nombre_fiscal,
            'nombre_comercial' => $this->empresa?->nombre_comercial,
            'nif' => $this->empresa?->nif,
            'tipo_empresa' => $this->empresa?->tipoEmpresa?->nombre,
            'estado_verificacion' => $this->estadoVerificacion?->nombre,
            'estado_identidad' => $this->estado_identidad ?? 'pendiente',
            'estado_empresa' => $this->estado_empresa ?? 'pendiente',
            'estado_representacion' => $this->estado_representacion,
            'observaciones' => $this->observaciones,
            'motivo_rechazo' => $this->motivo_rechazo,
            'fecha_revision' => $this->fecha_revision,
            'created_at' => $this->created_at,
            'responsable' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'nombre' => $this->user?->nombre,
                'apellido1' => $this->user?->apellido1,
                'apellido2' => $this->user?->apellido2,
                'email' => $this->user?->email,
                'telefono' => $this->user?->telefono,
                'activo' => (bool) $this->user?->activo,
            ]),
            'empresa' => $this->whenLoaded('empresa', fn () => [
                'id' => $this->empresa?->id,
                'nombre_fiscal' => $this->empresa?->nombre_fiscal,
                'nombre_comercial' => $this->empresa?->nombre_comercial,
                'nif' => $this->empresa?->nif,
                'correo' => $this->empresa?->correo,
                'telefono' => $this->empresa?->telefono,
                'direccion_fiscal' => $this->empresa?->direccion_fiscal,
                'codigo_postal' => $this->empresa?->codigo_postal,
                'municipio' => $this->empresa?->municipio,
                'provincia' => $this->empresa?->provincia,
                'pais' => $this->empresa?->pais,
                'activa' => (bool) $this->empresa?->activa,
                'tipo_empresa' => $this->empresa?->tipoEmpresa?->nombre,
            ]),
            'documentos' => DocumentoVerificacionResource::collection($this->whenLoaded('documentos')),
            'historial' => $this->whenLoaded('eventosAdmin', fn () => $this->eventosAdmin->map(fn ($evento) => [
                'id' => $evento->id,
                'accion' => $evento->accion,
                'estado_anterior' => $evento->estado_anterior,
                'estado_nuevo' => $evento->estado_nuevo,
                'motivo' => $evento->motivo,
                'metadatos' => $evento->metadatos,
                'created_at' => $evento->created_at,
                'admin' => $evento->relationLoaded('admin') ? [
                    'id' => $evento->admin?->id,
                    'nombre' => $evento->admin?->nombre ?? $evento->admin?->name,
                    'email' => $evento->admin?->email,
                ] : null,
            ])),
        ];
    }
}

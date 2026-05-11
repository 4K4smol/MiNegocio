<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;

class AdminUsuarioDetalleResource extends AdminUsuarioResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
            'verificacion' => $this->whenLoaded('verificacion', fn () => $this->verificacion ? [
                'id' => $this->verificacion->id,
                'estado_verificacion' => $this->verificacion->estadoVerificacion?->nombre,
                'numero_documento' => $this->verificacion->numero_documento,
                'fecha_verificacion' => $this->verificacion->fecha_verificacion,
                'notas_revision' => $this->verificacion->notas_revision,
            ] : null),
            'solicitudes_verificacion' => $this->whenLoaded('solicitudesVerificacion', fn () =>
                AdminSolicitudVerificacionResource::collection(
                    $this->solicitudesVerificacion->pluck('empresa')->filter()
                )
            ),
        ];
    }
}

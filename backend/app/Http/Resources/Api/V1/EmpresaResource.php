<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_fiscal' => $this->nombre_fiscal,
            'nombre_comercial' => $this->nombre_comercial,
            'nif' => $this->nif,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'direccion_fiscal' => $this->direccion_fiscal,
            'codigo_postal' => $this->codigo_postal,
            'municipio' => $this->municipio,
            'provincia' => $this->provincia,
            'pais' => $this->pais,
            'activa' => $this->activa,
            'tipo_empresa_id' => $this->tipo_empresa_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tipo_empresa' => $this->whenLoaded('tipoEmpresa'),
            'usuarios' => $this->whenLoaded('usuarios', fn () => $this->usuarios->map(fn ($usuario) => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre ?? $usuario->name,
                'email' => $usuario->email,
                'activo' => (bool) $usuario->activo,
                'role' => $usuario->relationLoaded('role') ? $usuario->role?->nombre : null,
            ])),
            'modulos' => $this->whenLoaded('modulos', fn () => $this->modulos->map(fn ($modulo) => [
                'id' => $modulo->id,
                'nombre' => $modulo->nombre,
                'slug' => $modulo->slug,
                'activo' => (bool) ($modulo->pivot?->activo ?? false),
            ])),
            'solicitudes_verificacion' => $this->whenLoaded('solicitudesVerificacion', fn () =>
                SolicitudRegistroResource::collection($this->solicitudesVerificacion)
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaHistorial;
use App\Models\User;

class FacturaHistorialService
{
    public function registrar(
        Factura $factura,
        string $accion,
        ?User $user = null,
        ?int $estadoAnteriorId = null,
        ?int $estadoNuevoId = null,
        ?string $descripcion = null,
        array $metadatos = []
    ): FacturaHistorial {
        return FacturaHistorial::query()->create([
            'factura_id' => $factura->id,
            'empresa_id' => $factura->empresa_id,
            'user_id' => $user?->id,
            'accion' => $accion,
            'estado_anterior_id' => $estadoAnteriorId,
            'estado_nuevo_id' => $estadoNuevoId,
            'descripcion' => $descripcion,
            'metadatos' => $metadatos ?: null,
        ]);
    }
}

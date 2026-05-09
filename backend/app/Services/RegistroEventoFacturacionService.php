<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RegistroEventoFacturacion;

class RegistroEventoFacturacionService
{
    public function registrar(array $data): RegistroEventoFacturacion
    {
        return RegistroEventoFacturacion::query()->create([
            'empresa_id' => $data['empresa_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'factura_id' => $data['factura_id'] ?? null,
            'registro_facturacion_id' => $data['registro_facturacion_id'] ?? null,
            'codigo_evento' => (string) ($data['codigo_evento'] ?? 'EVENTO_TECNICO'),
            'descripcion' => $data['descripcion'] ?? null,
            'payload_json' => $data['payload_json'] ?? null,
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'generado_at' => $data['generado_at'] ?? now(),
        ]);
    }
}

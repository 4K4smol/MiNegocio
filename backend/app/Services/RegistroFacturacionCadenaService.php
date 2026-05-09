<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RegistroFacturacion;

class RegistroFacturacionCadenaService
{
    public function validarCadenaEmpresa(int $empresaId): array
    {
        $registros = RegistroFacturacion::query()->where('empresa_id', $empresaId)->orderBy('id')->get();
        $errores = [];
        $anterior = null;
        foreach ($registros as $registro) {
            if ($anterior && $registro->registro_anterior_hash_64 !== $anterior->hash_actual) {
                $errores[] = "Registro {$registro->id} no enlaza con hash anterior.";
            }
            $anterior = $registro;
        }

        return ['valida' => count($errores) === 0, 'total_registros' => $registros->count(), 'errores' => $errores];
    }

    public function recalcularHashDesdeRegistro(RegistroFacturacion $registro): string
    {
        return hash('sha256', json_encode([
            'tipo' => $registro->tipoRegistroFacturacion?->codigo,
            'factura_id' => $registro->factura_id,
            'serie' => $registro->serie,
            'numero' => $registro->numero,
            'fecha_expedicion' => optional($registro->fecha_expedicion)->toDateString(),
            'importe_total' => $registro->importe_total,
            'registro_anterior_hash_64' => $registro->registro_anterior_hash_64,
            'generado_at' => optional($registro->generado_at)->toISOString(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}

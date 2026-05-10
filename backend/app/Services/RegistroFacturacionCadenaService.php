<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RegistroFacturacion;

class RegistroFacturacionCadenaService
{
    public function __construct(private readonly RegistroFacturacionService $registroFacturacionService) {}

    public function validarCadenaEmpresa(int $empresaId): array
    {
        $registros = RegistroFacturacion::query()
            ->with(['factura', 'tipoRegistroFacturacion'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->get();

        $errores = [];
        $anterior = null;

        foreach ($registros as $indice => $registro) {
            if ($indice === 0) {
                if (! $registro->primer_registro_cadena) {
                    $errores[] = $this->error($registro, 'El primer registro debe tener primer_registro_cadena=true.');
                }

                if ($registro->registro_anterior_hash_64 !== null) {
                    $errores[] = $this->error($registro, 'El primer registro no debe tener registro_anterior_hash_64.');
                }
            } else {
                if ($registro->primer_registro_cadena) {
                    $errores[] = $this->error($registro, 'Solo el primer registro puede tener primer_registro_cadena=true.');
                }

                if ($registro->registro_anterior_hash_64 !== $anterior?->hash_actual) {
                    $errores[] = $this->error($registro, 'No enlaza con el hash_actual del registro anterior.', [
                        'hash_anterior_esperado' => $anterior?->hash_actual,
                        'hash_anterior_informado' => $registro->registro_anterior_hash_64,
                    ]);
                }
            }

            $hashRecalculado = $this->recalcularHashDesdeRegistro($registro);

            if ($registro->hash_actual !== $hashRecalculado) {
                $errores[] = $this->error($registro, 'El hash_actual no coincide con el hash recalculado.', [
                    'hash_actual' => $registro->hash_actual,
                    'hash_recalculado' => $hashRecalculado,
                ]);
            }

            $anterior = $registro;
        }

        return [
            'valida' => count($errores) === 0,
            'empresa_id' => $empresaId,
            'total_registros' => $registros->count(),
            'errores' => $errores,
            'comprobado_at' => now()->toISOString(),
        ];
    }

    public function recalcularHashDesdeRegistro(RegistroFacturacion $registro): string
    {
        return $this->registroFacturacionService->recalcularHashDesdeRegistro($registro);
    }

    private function error(RegistroFacturacion $registro, string $motivo, array $detalle = []): array
    {
        return [
            'registro_id' => $registro->id,
            'empresa_id' => $registro->empresa_id,
            'tipo_registro' => $registro->tipoRegistroFacturacion?->codigo,
            'serie' => $registro->serie,
            'numero' => $registro->numero,
            'fecha_expedicion' => $registro->fecha_expedicion?->toDateString(),
            'motivo' => $motivo,
            'detalle' => $detalle,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SecuenciaFacturacion;
use Illuminate\Support\Facades\DB;

class NumeracionFacturaService
{
    /**
     * Debe ejecutarse dentro de una transaccion de emision. Bloquea solo la fila
     * empresa+ejercicio+serie y evita agregaciones bloqueadas no compatibles con PostgreSQL.
     *
     * @return array{serie:string, numero:string, numero_completo:string, secuencial:int}
     */
    public function siguiente(int $empresaId, string $serie, ?int $ejercicio = null): array
    {
        $ejercicio ??= (int) now()->year;

        SecuenciaFacturacion::query()->firstOrCreate(
            ['empresa_id' => $empresaId, 'ejercicio' => $ejercicio, 'serie' => $serie],
            ['ultimo_numero' => 0]
        );

        /** @var SecuenciaFacturacion $secuencia */
        $secuencia = SecuenciaFacturacion::query()
            ->where('empresa_id', $empresaId)
            ->where('ejercicio', $ejercicio)
            ->where('serie', $serie)
            ->lockForUpdate()
            ->firstOrFail();

        $secuencia->ultimo_numero++;
        $secuencia->save();

        $numero = sprintf('%d-%06d', $ejercicio, $secuencia->ultimo_numero);

        return [
            'serie' => $serie,
            'numero' => $numero,
            'numero_completo' => $serie . '-' . $numero,
            'secuencial' => (int) $secuencia->ultimo_numero,
        ];
    }

    public function existeUsoDeAgregacionBloqueada(): bool
    {
        return false;
    }
}

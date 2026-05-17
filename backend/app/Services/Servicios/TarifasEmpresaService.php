<?php

declare(strict_types=1);

namespace App\Services\Servicios;

use App\Models\ServicioTarifa;

class TarifasEmpresaService
{
    private const TARIFAS_BASE = [
        [
            'codigo' => 'estandar',
            'nombre' => 'Estándar',
            'descripcion' => 'Tarifa habitual del servicio',
            'es_default' => true,
            'activo' => true,
        ],
        [
            'codigo' => 'urgente',
            'nombre' => 'Urgente',
            'descripcion' => 'Precio para trabajos urgentes',
            'es_default' => false,
            'activo' => true,
        ],
        [
            'codigo' => 'especial',
            'nombre' => 'Especial',
            'descripcion' => 'Precio pactado o especial',
            'es_default' => false,
            'activo' => true,
        ],
        [
            'codigo' => 'fin_semana',
            'nombre' => 'Fin de semana',
            'descripcion' => 'Precio para trabajos en fin de semana',
            'es_default' => false,
            'activo' => true,
        ],
    ];

    public function asegurarTarifasBaseParaEmpresa(int $empresaId): void
    {
        foreach (self::TARIFAS_BASE as $tarifa) {
            ServicioTarifa::query()->firstOrCreate(
                ['empresa_id' => $empresaId, 'codigo' => $tarifa['codigo']],
                array_merge($tarifa, ['empresa_id' => $empresaId]),
            );
        }
    }

    public function obtenerTarifaDefault(int $empresaId): ServicioTarifa
    {
        $this->asegurarTarifasBaseParaEmpresa($empresaId);

        return ServicioTarifa::query()
            ->where('empresa_id', $empresaId)
            ->where('es_default', true)
            ->where('activo', true)
            ->firstOrFail();
    }
}

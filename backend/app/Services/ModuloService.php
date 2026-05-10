<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmpresaModulo;
use App\Models\Modulo;
use App\Models\User;
use RuntimeException;

class ModuloService
{
    private const ROL_ADMIN = 'admin';

    public function empresaTieneModulo(User $user, string $codigoModulo): bool
    {
        if (strtolower((string) $user->role?->nombre) === self::ROL_ADMIN) {
            return true;
        }

        if ($user->empresa_id === null) {
            return false;
        }

        return EmpresaModulo::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('activo', true)
            ->whereHas('modulo', function ($query) use ($codigoModulo): void {
                $query->where('activo', true)
                    ->whereRaw('LOWER(codigo) = ?', [strtolower($codigoModulo)]);
            })
            ->exists();
    }

    public function activarModulo(int $empresaId, string $codigoModulo): EmpresaModulo
    {
        $modulo = $this->obtenerModuloPorCodigo($codigoModulo);

        return EmpresaModulo::query()->updateOrCreate(
            [
                'empresa_id' => $empresaId,
                'modulo_id' => $modulo->id,
            ],
            [
                'activo' => true,
                'fecha_activacion' => now(),
                'fecha_desactivacion' => null,
            ]
        );
    }

    public function desactivarModulo(int $empresaId, string $codigoModulo): void
    {
        $modulo = $this->obtenerModuloPorCodigo($codigoModulo);

        EmpresaModulo::query()
            ->where('empresa_id', $empresaId)
            ->where('modulo_id', $modulo->id)
            ->update([
                'activo' => false,
                'fecha_desactivacion' => now(),
            ]);
    }

    private function obtenerModuloPorCodigo(string $codigoModulo): Modulo
    {
        $modulo = Modulo::query()
            ->whereRaw('LOWER(codigo) = ?', [strtolower($codigoModulo)])
            ->first();

        if (! $modulo) {
            throw new RuntimeException('No existe el módulo "'.$codigoModulo.'".');
        }

        return $modulo;
    }
}

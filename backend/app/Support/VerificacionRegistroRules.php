<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

class VerificacionRegistroRules
{
    public static function normalizar(?string $valor): string
    {
        return Str::of((string) $valor)
            ->lower()
            ->ascii()
            ->trim()
            ->toString();
    }

    public static function requiereRepresentacion(?string $tipoEmpresa): bool
    {
        $normalizado = self::normalizar($tipoEmpresa);

        return $normalizado === '' || ! str_contains($normalizado, 'autonomo');
    }

    public static function requiereReversoDocumento(?string $tipoDocumento): bool
    {
        $normalizado = self::normalizar($tipoDocumento);

        return $normalizado === 'dni'
            || $normalizado === 'nie'
            || str_contains($normalizado, 'dni')
            || str_contains($normalizado, 'nie');
    }
}

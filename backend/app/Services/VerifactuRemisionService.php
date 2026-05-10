<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\Verifactu\VerifactuRealClient;
use App\Services\Verifactu\VerifactuSimuladoClient;
use RuntimeException;

class VerifactuRemisionService
{
    public function __construct(
        private readonly VerifactuSimuladoClient $simuladoClient,
        private readonly VerifactuRealClient $realClient
    ) {}

    public function enviarPendientes(User $user): array
    {
        $modo = (string) config('services.verifactu.modo', 'simulado');

        return match ($modo) {
            'simulado' => $this->simuladoClient->enviarPendientes($user),
            'real' => $this->realClient->enviarPendientes($user),
            default => throw new RuntimeException('Modo VeriFactu no válido: '.$modo.'. Use "simulado" o "real".'),
        };
    }
}

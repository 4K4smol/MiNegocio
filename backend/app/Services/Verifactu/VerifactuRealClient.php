<?php

declare(strict_types=1);

namespace App\Services\Verifactu;

use App\Exceptions\VerifactuRealNoConfiguradoException;
use App\Models\User;

class VerifactuRealClient implements VerifactuClientInterface
{
    public function enviarPendientes(User $user): array
    {
        $endpoint = config('services.verifactu.endpoint');
        $certPath = config('services.verifactu.cert_path');
        $certPassword = config('services.verifactu.cert_password');

        if (! $endpoint || ! $certPath || ! $certPassword) {
            throw new VerifactuRealNoConfiguradoException();
        }

        throw new VerifactuRealNoConfiguradoException();
    }
}

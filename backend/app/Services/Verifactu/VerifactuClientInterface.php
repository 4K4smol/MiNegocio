<?php

declare(strict_types=1);

namespace App\Services\Verifactu;

use App\Models\User;

interface VerifactuClientInterface
{
    public function enviarPendientes(User $user): array;
}

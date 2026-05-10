<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class VerifactuRealNoConfiguradoException extends RuntimeException
{
    public function __construct(
        string $message = 'La remisión real VeriFactu no está configurada. Use modo simulado o configure endpoint y certificado.'
    ) {
        parent::__construct($message);
    }
}

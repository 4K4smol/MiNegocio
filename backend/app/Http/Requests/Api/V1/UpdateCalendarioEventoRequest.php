<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

class UpdateCalendarioEventoRequest extends StoreCalendarioEventoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['titulo'][0] = 'sometimes';
        $rules['inicio'][0] = 'sometimes';
        $rules['tipo'][0] = 'sometimes';

        return $rules;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

class UpdateFacturaRequest extends StoreFacturaRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['cliente_id'] = ['sometimes', 'integer', 'exists:clientes,id'];

        return $rules;
    }
}

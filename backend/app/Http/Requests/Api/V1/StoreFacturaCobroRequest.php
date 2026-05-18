<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacturaCobroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_cobro' => ['sometimes', 'date'],
            'importe' => ['required', 'numeric', 'gt:0'],
            'metodo_pago' => ['required', 'string', 'max:60'],
            'referencia' => ['sometimes', 'nullable', 'string', 'max:255'],
            'observaciones' => ['sometimes', 'nullable', 'string'],
        ];
    }
}

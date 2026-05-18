<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class EmitirFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serie' => ['sometimes', 'nullable', 'string', 'max:20'],
            'ejercicio' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'fecha_emision' => ['sometimes', 'date'],
            'fecha_operacion' => ['sometimes', 'nullable', 'date'],
        ];
    }
}

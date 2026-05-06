<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarioUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'nombre' => ['sometimes','string','max:255'],
            'descripcion' => ['nullable','string'],
            'tipo' => ['nullable','string','max:50'],
            'activo' => ['sometimes','boolean'],
        ];
    }
}

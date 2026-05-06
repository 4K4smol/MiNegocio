<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['sometimes','string','max:50'],
            'nombre' => ['sometimes','string','max:255'],
            'descripcion' => ['nullable','string'],
            'activo' => ['sometimes','boolean'],
            'orden_visual' => ['nullable','integer'],
            'icono' => ['nullable','string','max:255'],
        ];
    }
}

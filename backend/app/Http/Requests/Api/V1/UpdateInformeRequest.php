<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInformeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['sometimes','integer','exists:empresas,id'],
            'generado_por' => ['nullable','integer','exists:users,id'],
            'codigo' => ['sometimes','string','max:50'],
            'nombre' => ['sometimes','string','max:255'],
            'tipo' => ['sometimes','string','max:50'],
            'formato' => ['sometimes','string','max:50'],
            'estado_codigo' => ['sometimes','string','max:50'],
            'filtros' => ['nullable','array'],
            'resumen' => ['nullable','array'],
            'contenido' => ['nullable','string'],
            'generado_en' => ['nullable','date'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreInformeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required','integer','exists:empresas,id'],
            'generado_por' => ['nullable','integer','exists:users,id'],
            'codigo' => ['required','string','max:50'],
            'nombre' => ['required','string','max:255'],
            'tipo' => ['required','string','max:50'],
            'formato' => ['required','string','max:50'],
            'estado_codigo' => ['required','string','max:50'],
            'filtros' => ['nullable','array'],
            'resumen' => ['nullable','array'],
            'contenido' => ['nullable','string'],
            'generado_en' => ['nullable','date'],
        ];
    }
}

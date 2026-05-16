<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerificacionUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento_identidad_id' => ['required', 'integer', 'exists:tipos_documento_identidad,id'],
            'numero_documento'            => ['required', 'string', 'max:100'],
            'ruta_documento_anverso'      => ['nullable', 'string', 'max:255'],
            'ruta_documento_reverso'      => ['nullable', 'string', 'max:255'],
            'ruta_selfie'                 => ['nullable', 'string', 'max:255'],
        ];
    }
}

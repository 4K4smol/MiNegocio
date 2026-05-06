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
            'user_id' => ['required','integer','exists:users,id'],
            'estado_verificacion_id' => ['required','integer','exists:estados_verificacion,id'],
            'tipo_documento_identidad_id' => ['required','integer','exists:tipos_documento_identidad,id'],
            'numero_documento' => ['required','string','max:100'],
            'ruta_documento_anverso' => ['nullable','string','max:255'],
            'ruta_documento_reverso' => ['nullable','string','max:255'],
            'ruta_selfie' => ['nullable','string','max:255'],
            'fecha_verificacion' => ['nullable','date'],
            'notas_revision' => ['nullable','string'],
            'revisado_por' => ['nullable','integer','exists:users,id'],
        ];
    }
}

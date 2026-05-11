<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarSubsanacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:5000'],
            'documentos_requeridos' => ['nullable', 'array'],
            'documentos_requeridos.*' => ['string', 'in:identidad,empresa_actividad,representacion,otros'],
        ];
    }
}

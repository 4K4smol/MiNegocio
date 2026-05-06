<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_trabajo_id' => ['sometimes','integer','exists:ordenes_trabajo,id'],
            'orden_trabajo_linea_id' => ['nullable','integer','exists:orden_trabajo_lineas,id'],
            'responsable_id' => ['nullable','integer','exists:users,id'],
            'titulo' => ['sometimes','string','max:255'],
            'descripcion' => ['nullable','string'],
            'estado_codigo' => ['sometimes','string','max:50'],
            'fecha_vencimiento' => ['nullable','date'],
            'fecha_completada' => ['nullable','date'],
            'orden' => ['nullable','integer'],
            'activa' => ['sometimes','boolean'],
        ];
    }
}

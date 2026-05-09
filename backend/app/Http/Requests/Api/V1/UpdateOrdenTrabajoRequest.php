<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\OrdenTrabajo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrdenTrabajoRequest extends StoreOrdenTrabajoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['cliente_id'][0] = 'sometimes';
        $rules['lineas'][0] = 'sometimes';

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            /** @var OrdenTrabajo|null $orden */
            $orden = $this->route('orden');
            if (! $orden instanceof OrdenTrabajo) { return; }

            $estado = $orden->estado?->codigo;
            if (in_array($estado, ['completada', 'facturada'], true) && $this->has('lineas')) {
                $validator->errors()->add('lineas', 'No se pueden modificar líneas de una orden completada o facturada.');
            }
        });
    }
}

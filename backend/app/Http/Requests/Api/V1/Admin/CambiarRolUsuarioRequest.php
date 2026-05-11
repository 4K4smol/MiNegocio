<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CambiarRolUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}

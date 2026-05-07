<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RechazarSolicitudRegistroRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['motivo_rechazo' => ['required', 'string', 'min:5']]; }
}

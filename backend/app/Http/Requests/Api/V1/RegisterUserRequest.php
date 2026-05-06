<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // Usuario responsable
            'usuario' => ['required', 'array'],
            'usuario.nombre' => ['required', 'string', 'max:255'],
            'usuario.apellido1' => ['required', 'string', 'max:255'],
            'usuario.apellido2' => ['nullable', 'string', 'max:255'],
            'usuario.telefono' => ['nullable', 'string', 'max:30'],
            'usuario.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'usuario.password' => ['required', 'string', 'min:8', 'confirmed'],
            'usuario.tipo_documento_identidad_id' => ['required', 'integer', 'exists:tipos_documento_identidad,id'],
            'usuario.numero_documento' => ['required', 'string', 'max:50'],

            // Empresa o autónomo
            'empresa' => ['required', 'array'],
            'empresa.tipo_empresa_id' => ['required', 'integer', 'exists:tipos_empresa,id'],
            'empresa.nombre_fiscal' => ['required', 'string', 'max:255'],
            'empresa.nombre_comercial' => ['nullable', 'string', 'max:255'],
            'empresa.nif' => ['required', 'string', 'max:30', 'unique:empresas,nif'],
            'empresa.correo' => ['nullable', 'email', 'max:255'],
            'empresa.telefono' => ['nullable', 'string', 'max:30'],
            'empresa.direccion_fiscal' => ['nullable', 'string', 'max:255'],
            'empresa.codigo_postal' => ['nullable', 'string', 'max:20'],
            'empresa.municipio' => ['nullable', 'string', 'max:100'],
            'empresa.provincia' => ['nullable', 'string', 'max:100'],
            'empresa.pais' => ['nullable', 'string', 'max:100'],

            // Documentación de verificación
            'documentacion' => ['required', 'array'],
            'documentacion.ruta_documento_anverso' => ['required', 'string', 'max:255'],
            'documentacion.ruta_documento_reverso' => ['nullable', 'string', 'max:255'],
            'documentacion.ruta_selfie' => ['nullable', 'string', 'max:255'],

            'documentacion.ruta_documento_fiscal' => ['nullable', 'string', 'max:255'],
            'documentacion.ruta_registro_mercantil' => ['nullable', 'string', 'max:255'],
            'documentacion.ruta_documento_representacion' => ['nullable', 'string', 'max:255'],
            'documentacion.ruta_poder_apoderamiento' => ['nullable', 'string', 'max:255'],
            'documentacion.referencia_certificado_digital' => ['nullable', 'string', 'max:255'],
        ];
    }
}

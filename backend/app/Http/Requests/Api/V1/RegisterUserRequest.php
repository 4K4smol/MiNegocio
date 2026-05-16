<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\TipoEmpresa;
use App\Models\TipoDocumentoIdentidad;
use App\Support\VerificacionRegistroRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
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
            'usuario.password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'usuario.tipo_documento_identidad_id' => [
                'required',
                'integer',
                'exists:tipos_documento_identidad,id',
            ],
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

            'documentacion.dni_frontal' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.dni_reverso' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.selfie' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'documentacion.documento_fiscal' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.registro_mercantil' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.documento_representacion' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.poder_apoderamiento' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
            'documentacion.referencia_certificado_digital' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tipoDocumentoId = $this->input('usuario.tipo_documento_identidad_id');

            if ($tipoDocumentoId !== null) {
                $tipoDocumento = TipoDocumentoIdentidad::query()->find((int) $tipoDocumentoId);

                if (
                    $tipoDocumento !== null
                    && VerificacionRegistroRules::requiereReversoDocumento($tipoDocumento->nombre)
                    && ! $this->hasFile('documentacion.dni_reverso')
                ) {
                    $validator->errors()->add(
                        'documentacion.dni_reverso',
                        'El reverso del DNI/NIE es obligatorio para este tipo de documento.'
                    );
                }
            }

            $tipoEmpresaId = $this->input('empresa.tipo_empresa_id');

            if ($tipoEmpresaId === null) {
                return;
            }

            $tipoEmpresa = TipoEmpresa::query()->find((int) $tipoEmpresaId);

            if ($tipoEmpresa === null) {
                return;
            }

            if (
                VerificacionRegistroRules::requiereRepresentacion($tipoEmpresa->nombre) &&
                ! $this->hasFile('documentacion.documento_representacion')
            ) {
                $validator->errors()->add(
                    'documentacion.documento_representacion',
                    'El documento de representación es obligatorio para sociedades, empresas o PYMES.'
                );
            }
        });
    }
}

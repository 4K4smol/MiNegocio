<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificacionEmpresa;
use App\Models\VerificacionUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RegistroEntidadService
{
    /**
     * @param array<string, mixed> $data
     * @return array{user: User, empresa: Empresa}
     */
    public function registrar(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $estadoPendiente = EstadoVerificacion::query()
                ->where('nombre', 'pendiente')
                ->first();

            if ($estadoPendiente === null) {
                throw new RuntimeException('No existe el estado de verificación pendiente.');
            }

            $rolEmpresa = Role::query()
                ->where('nombre', 'empresa_admin')
                ->first();

            if ($rolEmpresa === null) {
                throw new RuntimeException('No existe el rol empresa_admin.');
            }

            $empresa = Empresa::query()->create([
                'tipo_empresa_id' => $data['empresa']['tipo_empresa_id'],
                'nombre_fiscal' => $data['empresa']['nombre_fiscal'],
                'nombre_comercial' => $data['empresa']['nombre_comercial'] ?? null,
                'nif' => $data['empresa']['nif'],
                'correo' => $data['empresa']['correo'] ?? null,
                'telefono' => $data['empresa']['telefono'] ?? null,
                'direccion_fiscal' => $data['empresa']['direccion_fiscal'] ?? null,
                'codigo_postal' => $data['empresa']['codigo_postal'] ?? null,
                'municipio' => $data['empresa']['municipio'] ?? null,
                'provincia' => $data['empresa']['provincia'] ?? null,
                'pais' => $data['empresa']['pais'] ?? 'España',
                'activa' => false,
            ]);

            $user = User::query()->create([
                'name' => trim($data['usuario']['nombre'] . ' ' . $data['usuario']['apellido1']),
                'nombre' => $data['usuario']['nombre'],
                'apellido1' => $data['usuario']['apellido1'],
                'apellido2' => $data['usuario']['apellido2'] ?? null,
                'telefono' => $data['usuario']['telefono'] ?? null,
                'email' => $data['usuario']['email'],
                'password' => Hash::make($data['usuario']['password']),
                'empresa_id' => $empresa->id,
                'role_id' => $rolEmpresa->id,
                'activo' => false,
            ]);

            VerificacionUsuario::query()->create([
                'user_id' => $user->id,
                'estado_verificacion_id' => $estadoPendiente->id,
                'tipo_documento_identidad_id' => $data['usuario']['tipo_documento_identidad_id'],
                'numero_documento' => $data['usuario']['numero_documento'],
                'ruta_documento_anverso' => $data['documentacion']['ruta_documento_anverso'],
                'ruta_documento_reverso' => $data['documentacion']['ruta_documento_reverso'] ?? null,
                'ruta_selfie' => $data['documentacion']['ruta_selfie'] ?? null,
            ]);

            VerificacionEmpresa::query()->create([
                'empresa_id' => $empresa->id,
                'estado_verificacion_id' => $estadoPendiente->id,
                'ruta_documento_fiscal' => $data['documentacion']['ruta_documento_fiscal'] ?? null,
                'ruta_registro_mercantil' => $data['documentacion']['ruta_registro_mercantil'] ?? null,
                'ruta_documento_representacion' => $data['documentacion']['ruta_documento_representacion'] ?? null,
                'ruta_poder_apoderamiento' => $data['documentacion']['ruta_poder_apoderamiento'] ?? null,
                'referencia_certificado_digital' => $data['documentacion']['referencia_certificado_digital'] ?? null,
            ]);

            $user->load([
                'empresa.verificacion.estadoVerificacion',
                'verificacion.estadoVerificacion',
                'role',
            ]);

            $empresa->load([
                'verificacion.estadoVerificacion',
            ]);

            return [
                'user' => $user,
                'empresa' => $empresa,
            ];
        });
    }
}

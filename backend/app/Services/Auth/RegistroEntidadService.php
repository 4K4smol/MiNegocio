<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\Role;
use App\Models\User;
use App\Models\SolicitudVerificacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistroEntidadService
{
    public function registrar(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $pendiente = EstadoVerificacion::query()->where('nombre', 'pendiente')->firstOrFail();
            $rol = Role::query()->where('nombre', 'titular')->firstOrFail();

            $empresa = Empresa::query()->create([
                'tipo_empresa_id' => $data['tipo_empresa_id'],
                'nombre_fiscal' => $data['nombre_fiscal'],
                'nombre_comercial' => $data['nombre_comercial'] ?? null,
                'nif' => $data['nif'],
                'correo' => $data['correo_empresa'] ?? null,
                'telefono' => $data['telefono_empresa'] ?? null,
                'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
                'codigo_postal' => $data['codigo_postal'] ?? null,
                'municipio' => $data['municipio'] ?? null,
                'provincia' => $data['provincia'] ?? null,
                'pais' => $data['pais'] ?? 'España',
                'activa' => false,
            ]);

            $user = User::query()->create([
                'name' => trim($data['nombre'] . ' ' . $data['apellido1']),
                'nombre' => $data['nombre'],
                'apellido1' => $data['apellido1'],
                'apellido2' => $data['apellido2'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'empresa_id' => $empresa->id,
                'role_id' => $rol->id,
                'activo' => false,
            ]);

            $solicitud = SolicitudVerificacion::query()->create([
                'user_id' => $user->id,
                'empresa_id' => $empresa->id,
                'estado_verificacion_id' => $pendiente->id,
            ]);

            foreach (['dni_frontal', 'dni_reverso', 'documento_empresa', 'documento_representacion'] as $campo) {
                if (!empty($data[$campo]) && $data[$campo] instanceof UploadedFile) {
                    $this->guardarDocumento($data[$campo], $campo, $solicitud->id);
                }
            }


            return [
                'user' => $user->fresh(),
                'empresa' => $empresa->fresh()
            ];
        });
    }

    private function guardarDocumento(UploadedFile $file, string $tipo, int $solicitudId): DocumentoVerificacion
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $path = sprintf('%d/%s_%s.%s', $solicitudId, $tipo, $uuid, $ext);
        Storage::disk('verificaciones')->put($path, $file->getContent());

        return DocumentoVerificacion::query()->create([
            'solicitud_verificacion_id' => $solicitudId,
            'tipo_documento' => $tipo,
            'archivo' => $path,
            'nombre_original' => $file->getClientOriginalName(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificacionEmpresa;
use App\Models\VerificacionUsuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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

            $verificacion_usuario = VerificacionUsuario::query()->create(['user_id' => $user->id, 'estado_verificacion_id' => $pendiente->id, 'tipo_documento_identidad_id' => $data['tipo_documento_identidad_id'], 'numero_documento' => $data['numero_documento'], 'ruta_documento_anverso' => 'pendiente']);
            $verificacion_empresa = VerificacionEmpresa::query()->create(['empresa_id' => $empresa->id, 'estado_verificacion_id' => $pendiente->id]);

            foreach (['dni_frontal' => 'ruta_documento_anverso', 'dni_reverso' => 'ruta_documento_reverso'] as $campo => $attr) {
                if (!empty($data[$campo]) && $data[$campo] instanceof UploadedFile) {
                    $doc = $this->guardarDocumento($data[$campo], $campo, $pendiente->id, $user->id, $empresa->id, $verificacion_usuario->id, $verificacion_usuario->id);
                    $verificacion_usuario->{$attr} = $doc->ruta;
                }
            }
            if (!empty($data['documento_empresa']) && $data['documento_empresa'] instanceof UploadedFile) {
                $doc = $this->guardarDocumento($data['documento_empresa'], 'documento_empresa', $pendiente->id, $user->id, $empresa->id, $verificacion_usuario->id, $verificacion_usuario->id);
                $verificacion_empresa->ruta_documento_fiscal = $doc->ruta;
            }
            if (!empty($data['documento_representacion']) && $data['documento_representacion'] instanceof UploadedFile) {
                $doc = $this->guardarDocumento($data['documento_representacion'], 'documento_representacion', $pendiente->id, $user->id, $empresa->id, $verificacion_usuario->id, $verificacion_usuario->id);
                $verificacion_empresa->ruta_documento_representacion = $doc->ruta;
            }

            $verificacion_usuario->save();
            $verificacion_empresa->save();

            return ['user' => $user->fresh(), 'empresa' => $empresa->fresh()];
        });
    }

    private function guardarDocumento(UploadedFile $file, string $tipo, int $estadoId, int $userId, int $empresaId, int $vuId, int $veId): DocumentoVerificacion
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $now = now();
        $path = sprintf('verificaciones/%s/%s/empresa_%d/usuario_%d/%s_%s.%s', $now->format('Y'), $now->format('m'), $empresaId, $userId, $tipo, $uuid, $ext);
        Storage::disk('verificaciones')->put($path, $file->getContent());
        return DocumentoVerificacion::query()->create([
            'disco' => 'verificaciones',
            'ruta' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $ext,
            'tamano' => $file->getSize(),
            'hash_sha256' => hash_file('sha256', $file->getRealPath()),
            'tipo_documento' => $tipo,
            'estado_verificacion_id' => $estadoId,
            'user_id' => $userId,
            'empresa_id' => $empresaId,
            'verificacion_usuario_id' => $vuId,
            'verificacion_empresa_id' => $veId,
            'subido_por' => $userId,
        ]);
    }
}

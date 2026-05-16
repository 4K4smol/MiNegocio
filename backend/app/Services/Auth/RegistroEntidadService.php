<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\Role;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use App\Models\VerificacionEmpresa;
use App\Models\VerificacionUsuario;
use App\Support\VerificacionRegistroRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistroEntidadService
{
    public function registrar(array $data): array
    {
        $usuario_data = $data['usuario'] ?? [];
        $empresa_data = $data['empresa'] ?? [];
        $documentacion_data = $data['documentacion'] ?? [];

        return DB::transaction(function () use ($usuario_data, $empresa_data, $documentacion_data): array {
            $pendiente = EstadoVerificacion::query()
                ->where(
                    'nombre',
                    'pendiente'
                )->firstOrFail();

            $rol = Role::query()
                ->where(
                    'nombre',
                    'titular'
                )->firstOrFail();

            $empresa = Empresa::query()->create([
                'tipo_empresa_id' => $empresa_data['tipo_empresa_id'],
                'nombre_fiscal' => $empresa_data['nombre_fiscal'],
                'nombre_comercial' => $empresa_data['nombre_comercial'] ?? null,
                'nif' => $empresa_data['nif'],
                'correo' => $empresa_data['correo'] ?? null,
                'telefono' => $empresa_data['telefono'] ?? null,
                'direccion_fiscal' => $empresa_data['direccion_fiscal'] ?? null,
                'codigo_postal' => $empresa_data['codigo_postal'] ?? null,
                'municipio' => $empresa_data['municipio'] ?? null,
                'provincia' => $empresa_data['provincia'] ?? null,
                'pais' => $empresa_data['pais'] ?? 'España',
                'activa' => false,
            ]);

            $user = User::query()->create([
                'name' => trim($usuario_data['nombre'] . ' ' . $usuario_data['apellido1']),
                'nombre' => $usuario_data['nombre'],
                'apellido1' => $usuario_data['apellido1'],
                'apellido2' => $usuario_data['apellido2'] ?? null,
                'telefono' => $usuario_data['telefono'] ?? null,
                'email' => $usuario_data['email'],
                'password' => Hash::make($usuario_data['password']),
                'empresa_id' => $empresa->id,
                'role_id' => $rol->id,
                'activo' => false,
            ]);

            $requiereRepresentacion = VerificacionRegistroRules::requiereRepresentacion($empresa->tipoEmpresa?->nombre);

            $solicitud = SolicitudVerificacion::query()->create([
                'user_id' => $user->id,
                'empresa_id' => $empresa->id,
                'estado_verificacion_id' => $pendiente->id,
                'estado_identidad' => 'pendiente',
                'estado_empresa' => 'pendiente',
                'estado_representacion' => $requiereRepresentacion ? 'pendiente' : null,
            ]);

            $documentos = [];
            foreach (['dni_frontal', 'dni_reverso', 'selfie', 'documento_fiscal', 'registro_mercantil', 'documento_representacion', 'poder_apoderamiento'] as $campo) {
                if (!empty($documentacion_data[$campo]) && $documentacion_data[$campo] instanceof UploadedFile) {
                    $documentos[$campo] = $this->guardarDocumento($documentacion_data[$campo], $campo, $solicitud->id);
                }
            }

            VerificacionUsuario::query()->create([
                'user_id' => $user->id,
                'estado_verificacion_id' => $pendiente->id,
                'tipo_documento_identidad_id' => $usuario_data['tipo_documento_identidad_id'],
                'numero_documento' => $usuario_data['numero_documento'],
                'ruta_documento_anverso' => $documentos['dni_frontal']->archivo ?? null,
                'ruta_documento_reverso' => $documentos['dni_reverso']->archivo ?? null,
                'ruta_selfie' => $documentos['selfie']->archivo ?? null,
            ]);

            VerificacionEmpresa::query()->create([
                'empresa_id' => $empresa->id,
                'estado_verificacion_id' => $pendiente->id,
                'ruta_documento_fiscal' => $documentos['documento_fiscal']->archivo ?? null,
                'ruta_registro_mercantil' => $documentos['registro_mercantil']->archivo ?? null,
                'ruta_documento_representacion' => $documentos['documento_representacion']->archivo ?? null,
                'ruta_poder_apoderamiento' => $documentos['poder_apoderamiento']->archivo ?? null,
                'referencia_certificado_digital' => $documentacion_data['referencia_certificado_digital'] ?? null,
            ]);

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
        $content = $file->getContent();
        Storage::disk('verificaciones')->put($path, $content);

        return DocumentoVerificacion::query()->create([
            'solicitud_verificacion_id' => $solicitudId,
            'tipo_documento' => $tipo,
            'archivo' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'tamano' => $file->getSize(),
            'hash_sha256' => hash('sha256', $content),
        ]);
    }
}

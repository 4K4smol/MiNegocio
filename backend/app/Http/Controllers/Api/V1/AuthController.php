<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Models\User;
use App\Services\Auth\RegistroEntidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends ApiController
{
    private const ESTADO_APROBADA = 'aprobada';
    private const ROL_ADMIN = 'admin';

    public function register(RegistroEntidadService $registroEntidadService, RegisterUserRequest $request): JsonResponse
    {
        $resultado = $registroEntidadService->registrar($request->validated());

        return $this->created([
            'usuario' => [
                'id' => $resultado['user']->id,
                'nombre' => $resultado['user']->nombre ?? $resultado['user']->name,
                'email' => $resultado['user']->email,
                'activo' => (bool) $resultado['user']->activo,
            ],
            'empresa' => [
                'id' => $resultado['empresa']->id,
                'nombre_fiscal' => $resultado['empresa']->nombre_fiscal,
                'nif' => $resultado['empresa']->nif,
                'activa' => (bool) $resultado['empresa']->activa,
            ],
            'validaciones' => [
                'usuario' => 'pendiente',
                'empresa' => 'pendiente',
            ],
            'puede_acceder_crm' => false,
        ], 'Solicitud de alta registrada correctamente. Queda pendiente de validación administrativa.');
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciales inválidas.', 401);
        }

        $user->load([
            'empresa.verificacion.estadoVerificacion',
            'verificacion.estadoVerificacion',
            'role',
        ]);

        if ($user->role === null) {
            return $this->error('Tu cuenta no tiene un rol asignado.', 403);
        }

        if (! $user->activo) {
            return $this->error('Tu cuenta está pendiente de validación.', 403);
        }

        if ($this->esAdministrador($user)) {
            $token = $this->crearToken($user, $request, $data);

            return $this->success([
                ...$this->crearPayloadSesion($user),
                'token' => $token,
            ], 'Inicio de sesión exitoso.');
        }

        if (! $this->puedeAccederCrm($user)) {
            return $this->error('Tu cuenta o empresa todavía no tiene acceso completo al CRM.', 403, [
                'validaciones' => $this->obtenerEstadosValidacion($user),
            ]);
        }

        $token = $this->crearToken($user, $request, $data);

        return $this->success([
            ...$this->crearPayloadSesion($user),
            'token' => $token,
        ], 'Inicio de sesión exitoso.');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->load([
            'empresa.verificacion.estadoVerificacion',
            'verificacion.estadoVerificacion',
            'role',
        ]);

        return $this->success($this->crearPayloadSesion($user));
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return $this->success(null, 'Sesión cerrada correctamente.');
    }

    private function puedeAccederCrm(User $user): bool
    {
        if ($this->esAdministrador($user)) {
            return (bool) $user->activo;
        }

        $estadoUsuario = $user->verificacion?->estadoVerificacion?->nombre;
        $estadoEmpresa = $user->empresa?->verificacion?->estadoVerificacion?->nombre;

        return (bool) $user->activo
            && $user->role !== null
            && $user->empresa !== null
            && (bool) $user->empresa->activa
            && $estadoUsuario === self::ESTADO_APROBADA
            && $estadoEmpresa === self::ESTADO_APROBADA;
    }

    private function esAdministrador(User $user): bool
    {
        return $user->role?->nombre === self::ROL_ADMIN;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function crearToken(User $user, Request $request, array $data): string
    {
        return $user
            ->createToken($data['device_name'] ?? ($request->userAgent() ?? 'api-token'))
            ->plainTextToken;
    }

    /**
     * @return array<string, mixed>
     */
    private function crearPayloadSesion(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre ?? $user->name,
                'apellido1' => $user->apellido1,
                'apellido2' => $user->apellido2,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'activo' => (bool) $user->activo,
            ],
            'empresa' => $user->empresa === null ? null : [
                'id' => $user->empresa->id,
                'nombre_fiscal' => $user->empresa->nombre_fiscal,
                'nombre_comercial' => $user->empresa->nombre_comercial,
                'nif' => $user->empresa->nif,
                'activa' => (bool) $user->empresa->activa,
            ],
            'role' => $user->role === null ? null : [
                'id' => $user->role->id,
                'nombre' => $user->role->nombre,
            ],
            'validaciones' => $this->obtenerEstadosValidacion($user),
            'puede_acceder_crm' => $this->puedeAccederCrm($user),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function obtenerEstadosValidacion(User $user): array
    {
        return [
            'usuario' => $user->verificacion?->estadoVerificacion?->nombre,
            'empresa' => $user->empresa?->verificacion?->estadoVerificacion?->nombre,
        ];
    }
}

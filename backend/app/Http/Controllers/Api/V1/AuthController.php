<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginUserRequest;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends ApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Auth"},
     *     summary="Registrar usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Juan Perez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secreto123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secreto123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado")
     * )
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken($request->userAgent() ?? 'api-token')->plainTextToken;

        return $this->created([
            'user' => $user->toArray(),
            'token' => $token,
        ], 'Usuario registrado correctamente.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Auth"},
     *     summary="Iniciar sesión",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secreto123"),
     *             @OA\Property(property="device_name", type="string", example="web-chrome")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login exitoso"),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciales inválidas.', 401);
        }

        $token = $user->createToken($data['device_name'] ?? ($request->userAgent() ?? 'api-token'))->plainTextToken;

        return $this->success([
            'user' => $user->toArray(),
            'token' => $token,
        ], 'Inicio de sesión exitoso.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     tags={"Auth"},
     *     summary="Usuario autenticado actual",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Perfil del usuario")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(['user' => $user->toArray()]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Auth"},
     *     summary="Cerrar sesión",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Sesión cerrada")
     * )
     */
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
}

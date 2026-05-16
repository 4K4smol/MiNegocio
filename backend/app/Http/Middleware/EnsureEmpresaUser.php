<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(
                message: 'No autenticado.',
                statusCode: Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($this->isAdminUser($user)) {
            return $this->error(
                message: 'El administrador global no puede acceder al CRM de empresa.',
                statusCode: Response::HTTP_FORBIDDEN,
            );
        }

        if (empty($user->empresa_id)) {
            return $this->error(
                message: 'El usuario no tiene empresa asociada.',
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                errors: [
                    'empresa_id' => ['No se ha podido determinar la empresa asociada al usuario.'],
                ],
            );
        }

        return $next($request);
    }

    /**
     * @param object $user
     */
    private function isAdminUser(object $user): bool
    {
        return ($user->role?->nombre ?? null) === 'admin'
            || ($user->rol?->nombre ?? null) === 'admin'
            || ($user->role ?? null) === 'admin'
            || ($user->rol ?? null) === 'admin'
            || ($user->tipo ?? null) === 'admin';
    }

    /**
     * @param array<string, mixed>|list<mixed>|null $errors
     */
    private function error(string $message, int $statusCode, ?array $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?? (object) [],
        ], $statusCode);
    }
}

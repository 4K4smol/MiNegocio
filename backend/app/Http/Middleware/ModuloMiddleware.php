<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ModuloService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuloMiddleware
{
    public function __construct(private readonly ModuloService $moduloService) {}

    public function handle(Request $request, Closure $next, string $codigoModulo): Response
    {
        $user = $request->user();

        if ($user === null || !$this->moduloService->empresaTieneModulo($user, $codigoModulo)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'La empresa no tiene activo el módulo requerido: '.$codigoModulo.'.',
                'errors' => (object) [],
            ], 403);
        }

        return $next($request);
    }
}

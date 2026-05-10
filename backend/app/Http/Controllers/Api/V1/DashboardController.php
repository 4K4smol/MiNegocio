<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CalendarioEventoResource;
use App\Http\Resources\Api\V1\OrdenTrabajoResource;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function resumen(Request $request): JsonResponse
    {
        return $this->success($this->dashboardService->resumen($request->user()));
    }

    public function proximasOrdenes(Request $request): JsonResponse
    {
        $limite = (int) $request->integer('limite', 10);

        return $this->success(
            OrdenTrabajoResource::collection($this->dashboardService->proximasOrdenes($request->user(), $limite))->resolve()
        );
    }

    public function calendario(Request $request): JsonResponse
    {
        return $this->success(
            CalendarioEventoResource::collection(
                $this->dashboardService->calendario(
                    $request->user(),
                    $request->filled('desde') ? (string) $request->input('desde') : null,
                    $request->filled('hasta') ? (string) $request->input('hasta') : null
                )
            )->resolve()
        );
    }
}

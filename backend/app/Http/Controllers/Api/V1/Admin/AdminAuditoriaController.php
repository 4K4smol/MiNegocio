<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\Admin\AdminAuditoriaEventoResource;
use App\Models\AdminVerificacionEvento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuditoriaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $eventos = AdminVerificacionEvento::query()
            ->with(['admin', 'usuario', 'empresa'])
            ->when($request->filled('accion'), fn (Builder $query) =>
                $query->where('accion', $request->string('accion')->toString())
            )
            ->when($request->filled('user_admin_id'), fn (Builder $query) =>
                $query->where('user_admin_id', $request->integer('user_admin_id'))
            )
            ->when($request->filled('user_id'), fn (Builder $query) =>
                $query->where('user_id', $request->integer('user_id'))
            )
            ->when($request->filled('empresa_id'), fn (Builder $query) =>
                $query->where('empresa_id', $request->integer('empresa_id'))
            )
            ->when($request->filled('solicitud_verificacion_id'), fn (Builder $query) =>
                $query->where('solicitud_verificacion_id', $request->integer('solicitud_verificacion_id'))
            )
            ->when($request->filled('fecha_desde'), fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $request->date('fecha_desde'))
            )
            ->when($request->filled('fecha_hasta'), fn (Builder $query) =>
                $query->whereDate('created_at', '<=', $request->date('fecha_hasta'))
            )
            ->when($request->filled('texto'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('texto')->toString().'%';
                $query->where(function (Builder $search) use ($texto): void {
                    $search->where('accion', 'like', $texto)
                        ->orWhere('motivo', 'like', $texto)
                        ->orWhereHas('admin', fn (Builder $admin) => $admin->where('email', 'like', $texto)->orWhere('nombre', 'like', $texto))
                        ->orWhereHas('usuario', fn (Builder $usuario) => $usuario->where('email', 'like', $texto)->orWhere('nombre', 'like', $texto))
                        ->orWhereHas('empresa', fn (Builder $empresa) => $empresa->where('nombre_fiscal', 'like', $texto)->orWhere('nif', 'like', $texto));
                });
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 25));

        return $this->success(AdminAuditoriaEventoResource::collection($eventos)->response()->getData(true));
    }
}

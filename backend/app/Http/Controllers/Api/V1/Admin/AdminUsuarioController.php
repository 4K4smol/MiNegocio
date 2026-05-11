<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Admin\CambiarRolUsuarioRequest;
use App\Http\Resources\Api\V1\Admin\AdminUsuarioDetalleResource;
use App\Http\Resources\Api\V1\Admin\AdminUsuarioResource;
use App\Models\AdminVerificacionEvento;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUsuarioController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $usuarios = User::query()
            ->with(['role', 'empresa'])
            ->when($request->filled('texto'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('texto')->toString().'%';
                $query->where(function (Builder $search) use ($texto): void {
                    $search->where('name', 'like', $texto)
                        ->orWhere('nombre', 'like', $texto)
                        ->orWhere('apellido1', 'like', $texto)
                        ->orWhere('email', 'like', $texto)
                        ->orWhereHas('empresa', fn (Builder $empresa) => $empresa->where('nombre_fiscal', 'like', $texto)->orWhere('nif', 'like', $texto));
                });
            })
            ->when($request->filled('rol'), fn (Builder $query) =>
                $query->whereHas('role', fn (Builder $role) => $role->where('nombre', $request->string('rol')->toString()))
            )
            ->when($request->filled('role_id'), fn (Builder $query) =>
                $query->where('role_id', $request->integer('role_id'))
            )
            ->when($request->filled('activo'), fn (Builder $query) =>
                $query->where('activo', filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN))
            )
            ->when($request->filled('empresa'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('empresa')->toString().'%';
                $query->whereHas('empresa', fn (Builder $empresa) => $empresa->where('nombre_fiscal', 'like', $texto)->orWhere('nif', 'like', $texto));
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 25));

        return $this->success(AdminUsuarioResource::collection($usuarios)->response()->getData(true));
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(new AdminUsuarioDetalleResource($user->load([
            'role',
            'empresa.tipoEmpresa',
            'verificacion.estadoVerificacion',
            'solicitudesVerificacion.empresa.tipoEmpresa',
            'solicitudesVerificacion.estadoVerificacion',
        ])));
    }

    public function activar(Request $request, User $user): JsonResponse
    {
        $user->update(['activo' => true]);
        $this->registrarEvento($request, $user, 'activar_usuario', 'inactivo', 'activo');

        return $this->success(new AdminUsuarioResource($user->fresh(['role', 'empresa'])), 'Usuario activado correctamente.');
    }

    public function desactivar(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->is($user), 422, 'No puedes desactivar tu propio usuario administrador.');

        DB::transaction(function () use ($request, $user): void {
            $bloqueado = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $esAdmin = $bloqueado->role?->nombre === 'admin';

            if ($esAdmin && $this->administradoresActivos() <= 1) {
                abort(422, 'No se puede dejar el sistema sin administradores activos.');
            }

            $bloqueado->update(['activo' => false]);
            $this->registrarEvento($request, $bloqueado, 'desactivar_usuario', 'activo', 'inactivo');
        });

        return $this->success(new AdminUsuarioResource($user->fresh(['role', 'empresa'])), 'Usuario desactivado correctamente.');
    }

    public function cambiarRol(CambiarRolUsuarioRequest $request, User $user): JsonResponse
    {
        DB::transaction(function () use ($request, $user): void {
            $bloqueado = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $rolAnterior = $bloqueado->role?->nombre;
            $nuevoRol = Role::query()->findOrFail($request->validated('role_id'));

            if ($request->user()->is($bloqueado) && $rolAnterior === 'admin' && $nuevoRol->nombre !== 'admin' && $this->administradoresActivos() <= 1) {
                abort(422, 'No se puede quitar el ultimo administrador activo.');
            }

            $bloqueado->update(['role_id' => $nuevoRol->id]);
            $this->registrarEvento($request, $bloqueado, 'cambiar_rol_usuario', $rolAnterior, $nuevoRol->nombre);
        });

        return $this->success(new AdminUsuarioResource($user->fresh(['role', 'empresa'])), 'Rol actualizado correctamente.');
    }

    private function administradoresActivos(): int
    {
        return User::query()
            ->where('activo', true)
            ->whereHas('role', fn (Builder $query) => $query->where('nombre', 'admin'))
            ->count();
    }

    private function registrarEvento(Request $request, User $user, string $accion, ?string $anterior, ?string $nuevo): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'user_id' => $user->id,
            'empresa_id' => $user->empresa_id,
            'accion' => $accion,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
        ]);
    }
}

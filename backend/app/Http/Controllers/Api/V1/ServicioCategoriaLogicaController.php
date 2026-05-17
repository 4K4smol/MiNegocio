<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioCategoriaLogicaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $categorias = Servicio::query()
            ->select('tipo_negocio as nombre')
            ->selectRaw('COUNT(*) as total_servicios')
            ->selectRaw('SUM(CASE WHEN activo IS TRUE THEN 1 ELSE 0 END) as servicios_activos')
            ->where('empresa_id', $request->user()->empresa_id)
            ->whereNotNull('tipo_negocio')
            ->where('tipo_negocio', '<>', '')
            ->groupBy('tipo_negocio')
            ->orderBy('tipo_negocio')
            ->get()
            ->map(fn ($item) => [
                'nombre' => $item->nombre,
                'total_servicios' => (int) $item->total_servicios,
                'servicios_activos' => (int) $item->servicios_activos,
            ]);

        return $this->success($categorias);
    }

    public function renombrar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'actual' => ['required', 'string', 'max:50'],
            'nuevo' => ['required', 'string', 'max:50', 'different:actual'],
        ]);

        $actualizados = Servicio::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('tipo_negocio', $data['actual'])
            ->update(['tipo_negocio' => $data['nuevo']]);

        return $this->success(['actualizados' => $actualizados], 'Categoria renombrada correctamente.');
    }

    public function fusionar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origen' => ['required', 'string', 'max:50'],
            'destino' => ['required', 'string', 'max:50', 'different:origen'],
        ]);

        $actualizados = Servicio::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('tipo_negocio', $data['origen'])
            ->update(['tipo_negocio' => $data['destino']]);

        return $this->success(['actualizados' => $actualizados], 'Categorias fusionadas correctamente.');
    }

    public function vaciar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'categoria' => ['required', 'string', 'max:50'],
        ]);

        $actualizados = Servicio::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('tipo_negocio', $data['categoria'])
            ->update(['tipo_negocio' => null]);

        return $this->success(['actualizados' => $actualizados], 'Categoria vaciada correctamente.');
    }
}

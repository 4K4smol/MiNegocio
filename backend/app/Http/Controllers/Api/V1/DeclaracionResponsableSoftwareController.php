<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\DeclaracionResponsableSoftware;
use App\Services\DeclaracionResponsableSoftwareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeclaracionResponsableSoftwareController extends ApiController
{
    public function __construct(private readonly DeclaracionResponsableSoftwareService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $query = DeclaracionResponsableSoftware::query()->orderByDesc('id');

        if ($request->filled('codigo_software')) {
            $query->where('codigo_software', (string) $request->input('codigo_software'));
        }

        if ($request->filled('activa')) {
            $query->where('activa', $request->boolean('activa'));
        }

        return $this->success($query->paginate($perPage)->toArray());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = DeclaracionResponsableSoftware::query()->whereKey($id);
        $declaracion = $query->first();

        if (!$declaracion) {
            return $this->notFound();
        }

        return $this->success($declaracion->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_software' => ['nullable', 'string', 'max:120'],
            'codigo_software' => ['nullable', 'string', 'max:60'],
            'version_software' => ['nullable', 'string', 'max:50'],
            'productor_nombre' => ['nullable', 'string', 'max:150'],
            'productor_nif' => ['nullable', 'string', 'max:20'],
            'productor_direccion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'componentes' => ['nullable', 'array'],
            'modo_verifactu' => ['nullable', 'boolean'],
            'permite_multiples_obligados' => ['nullable', 'boolean'],
            'fecha_declaracion' => ['nullable', 'date'],
            'lugar_declaracion' => ['nullable', 'string', 'max:120'],
            'texto_declaracion' => ['nullable', 'string'],
            'pdf_path' => ['nullable', 'string', 'max:255'],
            'activa' => ['nullable', 'boolean'],
            'metadatos' => ['nullable', 'array'],
        ]);

        return $this->created(
            $this->service->crear($data)->toArray(),
            'Declaracion responsable de software creada.'
        );
    }
}

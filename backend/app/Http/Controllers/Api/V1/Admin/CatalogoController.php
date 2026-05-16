<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\EstadoVerificacion;
use App\Models\Modulo;
use App\Models\Role;
use App\Models\TipoDocumentoIdentidad;
use App\Models\TipoEmpresa;
use Illuminate\Http\JsonResponse;

class CatalogoController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success([
            'estados_verificacion' => EstadoVerificacion::query()->orderBy('id')->get(),
            'tipos_empresa' => TipoEmpresa::query()->orderBy('id')->get(),
            'tipos_documento_identidad' => TipoDocumentoIdentidad::query()->orderBy('id')->get(),
            'modulos' => Modulo::query()->orderBy('orden_visual')->orderBy('nombre')->get(),
            'roles' => Role::query()->orderBy('id')->get(),
            'readonly' => true,
        ]);
    }
}

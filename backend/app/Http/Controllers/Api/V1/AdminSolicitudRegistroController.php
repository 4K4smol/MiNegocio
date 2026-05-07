<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\RechazarSolicitudRegistroRequest;
use App\Http\Requests\Api\V1\SubsanarSolicitudRegistroRequest;
use App\Http\Resources\Api\V1\DocumentoVerificacionResource;
use App\Http\Resources\Api\V1\SolicitudRegistroResource;
use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSolicitudRegistroController extends ApiController
{
    public function index(): JsonResponse { return $this->success(SolicitudRegistroResource::collection(Empresa::with('verificacion.estadoVerificacion')->paginate(20))->response()->getData(true)); }
    public function show(Empresa $empresa): JsonResponse { $empresa->load('verificacion.estadoVerificacion','usuarios.verificacion.estadoVerificacion'); return $this->success(new SolicitudRegistroResource($empresa)); }

    public function aprobar(Request $request, Empresa $empresa): JsonResponse { return $this->cambiarEstado($request,$empresa,'aprobada'); }
    public function rechazar(RechazarSolicitudRegistroRequest $request, Empresa $empresa): JsonResponse { return $this->cambiarEstado($request,$empresa,'rechazada',$request->validated()['motivo_rechazo']); }
    public function subsanar(SubsanarSolicitudRegistroRequest $request, Empresa $empresa): JsonResponse { return $this->cambiarEstado($request,$empresa,'requiere_subsanacion',$request->validated()['observaciones']); }

    public function verDocumento(DocumentoVerificacion $documento)
    {
        abort_unless(Storage::disk($documento->disco)->exists($documento->ruta), 404);
        return Storage::disk($documento->disco)->response($documento->ruta);
    }

    private function cambiarEstado(Request $request, Empresa $empresa, string $estadoNombre, ?string $nota = null): JsonResponse
    {
        $estado = EstadoVerificacion::where('nombre', $estadoNombre)->firstOrFail();
        $empresa->verificacion()->update(['estado_verificacion_id'=>$estado->id,'notas_revision'=>$nota,'revisado_por'=>$request->user()->id,'fecha_verificacion'=>now()]);
        $empresa->usuarios()->update(['activo'=>$estadoNombre === 'aprobada']);
        $empresa->activa = $estadoNombre === 'aprobada'; $empresa->save();
        $empresa->usuarios()->each(fn($u)=>$u->verificacion()->update(['estado_verificacion_id'=>$estado->id,'notas_revision'=>$nota,'revisado_por'=>$request->user()->id,'fecha_verificacion'=>now()]));
        DocumentoVerificacion::where('empresa_id',$empresa->id)->update(['estado_verificacion_id'=>$estado->id,'revisado_por'=>$request->user()->id,'fecha_revision'=>now()]);
        return $this->success(new SolicitudRegistroResource($empresa->fresh('verificacion.estadoVerificacion')), 'Solicitud actualizada.');
    }
}

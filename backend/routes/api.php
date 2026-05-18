<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AdminEmpresaModuloController;
use App\Http\Controllers\Api\V1\Admin\AdminAuditoriaController;
use App\Http\Controllers\Api\V1\Admin\CatalogoController as AdminCatalogoController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminUsuarioController;
use App\Http\Controllers\Api\V1\Admin\DocumentoVerificacionController as AdminDocumentoVerificacionController;
use App\Http\Controllers\Api\V1\Admin\EmpresaController as AdminEmpresaController;
use App\Http\Controllers\Api\V1\Admin\SolicitudVerificacionController as AdminSolicitudVerificacionController;
use App\Http\Controllers\Api\V1\Admin\TipoTarifaServicioController as AdminTipoTarifaServicioController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CalendarioEventoController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DeclaracionResponsableSoftwareController;
use App\Http\Controllers\Api\V1\EstadoFacturaController;
use App\Http\Controllers\Api\V1\FacturaController;
use App\Http\Controllers\Api\V1\InformeController;
use App\Http\Controllers\Api\V1\InventarioItemController;
use App\Http\Controllers\Api\V1\InventarioMovimientoController;
use App\Http\Controllers\Api\V1\InventarioUbicacionController;
use App\Http\Controllers\Api\V1\InventarioUnidadMedidaController;
use App\Http\Controllers\Api\V1\ModuloController;
use App\Http\Controllers\Api\V1\OrdenTrabajoController;
use App\Http\Controllers\Api\V1\RegistroEventoController;
use App\Http\Controllers\Api\V1\RegistroEventoFacturacionController;
use App\Http\Controllers\Api\V1\RegistroFacturacionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ServicioController;
use App\Http\Controllers\Api\V1\ServicioPrecioController;
use App\Http\Controllers\Api\V1\TareaController;
use App\Http\Controllers\Api\V1\TipoTarifaServicioController;
use App\Http\Controllers\Api\V1\TipoClienteController;
use App\Http\Controllers\Api\V1\TipoDocumentoIdentidadController;
use App\Http\Controllers\Api\V1\TipoEmpresaController;
use App\Http\Controllers\Api\V1\TipoEventoFacturacionController;
use App\Http\Controllers\Api\V1\TipoFacturaController;
use App\Http\Controllers\Api\V1\TipoInventarioMovimientoController;
use App\Http\Controllers\Api\V1\TipoLocalizacionClienteController;
use App\Http\Controllers\Api\V1\TipoRectificacionController;
use App\Http\Controllers\Api\V1\TipoRegistroFacturacionController;
use App\Http\Controllers\Api\V1\VerifactuController;
use App\Http\Controllers\Api\V1\VerificacionUsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)->only(['index', 'show'])->parameters(['tipos-documento-identidad' => 'id']);
    Route::apiResource('tipos-empresa', TipoEmpresaController::class)->only(['index', 'show'])->parameters(['tipos-empresa' => 'id']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('estados-factura', EstadoFacturaController::class)->only(['index', 'show']);

        Route::middleware('admin')->group(function (): void {
            Route::apiResource('estados-factura', EstadoFacturaController::class)->except(['index', 'show'])->parameters(['estados-factura' => 'id']);

            Route::apiResource('modulos', ModuloController::class);
            Route::patch('modulos/{id}/activar', [ModuloController::class, 'activar']);
            Route::patch('modulos/{id}/desactivar', [ModuloController::class, 'desactivar']);

            Route::apiResource('roles', RoleController::class)->only(['index', 'show']);

            Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);

            Route::get('admin/solicitudes-verificacion', [AdminSolicitudVerificacionController::class, 'index']);
            Route::get('admin/solicitudes-verificacion/{empresa}', [AdminSolicitudVerificacionController::class, 'show']);
            Route::post('admin/solicitudes-verificacion/{empresa}/aprobar', [AdminSolicitudVerificacionController::class, 'aprobar']);
            Route::post('admin/solicitudes-verificacion/{empresa}/rechazar', [AdminSolicitudVerificacionController::class, 'rechazar']);
            Route::post('admin/solicitudes-verificacion/{empresa}/solicitar-subsanacion', [AdminSolicitudVerificacionController::class, 'solicitarSubsanacion']);

            Route::post('admin/solicitudes-verificacion/{empresa}/fases/identidad/aprobar', [AdminSolicitudVerificacionController::class, 'aprobarIdentidad']);
            Route::post('admin/solicitudes-verificacion/{empresa}/fases/identidad/rechazar', [AdminSolicitudVerificacionController::class, 'rechazarIdentidad']);
            Route::post('admin/solicitudes-verificacion/{empresa}/fases/empresa/aprobar', [AdminSolicitudVerificacionController::class, 'aprobarEmpresa']);
            Route::post('admin/solicitudes-verificacion/{empresa}/fases/empresa/rechazar', [AdminSolicitudVerificacionController::class, 'rechazarEmpresa']);
            Route::post('admin/solicitudes-verificacion/{empresa}/fases/representacion/aprobar', [AdminSolicitudVerificacionController::class, 'aprobarRepresentacion']);
            Route::post('admin/solicitudes-verificacion/{empresa}/fases/representacion/rechazar', [AdminSolicitudVerificacionController::class, 'rechazarRepresentacion']);

            Route::get('admin/documentos-verificacion/{documento}/preview', [AdminDocumentoVerificacionController::class, 'preview']);

            Route::get('admin/usuarios', [AdminUsuarioController::class, 'index']);
            Route::get('admin/usuarios/{user}', [AdminUsuarioController::class, 'show']);
            Route::patch('admin/usuarios/{user}/activar', [AdminUsuarioController::class, 'activar']);
            Route::patch('admin/usuarios/{user}/desactivar', [AdminUsuarioController::class, 'desactivar']);
            Route::patch('admin/usuarios/{user}/rol', [AdminUsuarioController::class, 'cambiarRol']);

            Route::get('admin/auditoria', [AdminAuditoriaController::class, 'index']);

            Route::get('admin/empresas', [AdminEmpresaController::class, 'index']);
            Route::get('admin/empresas/{empresa}', [AdminEmpresaController::class, 'show']);
            Route::patch('admin/empresas/{empresa}/activar', [AdminEmpresaController::class, 'activar']);
            Route::patch('admin/empresas/{empresa}/desactivar', [AdminEmpresaController::class, 'desactivar']);

            Route::get('admin/catalogos', [AdminCatalogoController::class, 'index']);

            Route::get('admin/empresas/{empresa}/modulos', [AdminEmpresaModuloController::class, 'index']);
            Route::post('admin/empresas/{empresa}/modulos/{modulo}/activar', [AdminEmpresaModuloController::class, 'activar']);
            Route::post('admin/empresas/{empresa}/modulos/{modulo}/desactivar', [AdminEmpresaModuloController::class, 'desactivar']);

            Route::apiResource('tipos-cliente', TipoClienteController::class)->except(['index', 'show'])->parameters(['tipos-cliente' => 'id']);
            Route::patch('tipos-cliente/{id}/activar', [TipoClienteController::class, 'activar']);
            Route::patch('tipos-cliente/{id}/desactivar', [TipoClienteController::class, 'desactivar']);

            Route::apiResource('tipos-localizacion-cliente', TipoLocalizacionClienteController::class)->except(['index', 'show'])->parameters(['tipos-localizacion-cliente' => 'id']);
            Route::patch('tipos-localizacion-cliente/{id}/activar', [TipoLocalizacionClienteController::class, 'activar']);
            Route::patch('tipos-localizacion-cliente/{id}/desactivar', [TipoLocalizacionClienteController::class, 'desactivar']);

            Route::apiResource('tipos-empresa', TipoEmpresaController::class)->except(['index', 'show'])->parameters(['tipos-empresa' => 'id']);
            Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)->except(['index', 'show'])->parameters(['tipos-documento-identidad' => 'id']);
            Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class)->except(['index', 'show'])->parameters(['tipos-evento-facturacion' => 'id']);
            Route::apiResource('tipos-factura', TipoFacturaController::class)->except(['index', 'show'])->parameters(['tipos-factura' => 'id']);
            Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class)->except(['index', 'show'])->parameters(['tipos-inventario-movimiento' => 'id']);
            Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class)->except(['index', 'show'])->parameters(['inventario-unidades-medida' => 'id']);
            Route::apiResource('tipos-rectificacion', TipoRectificacionController::class)->except(['index', 'show'])->parameters(['tipos-rectificacion' => 'id']);
            Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class)->except(['index', 'show'])->parameters(['tipos-registro-facturacion' => 'id']);

            Route::get('admin/verificaciones-usuario', [VerificacionUsuarioController::class, 'index']);
            Route::get('admin/verificaciones-usuario/{id}', [VerificacionUsuarioController::class, 'show']);
            Route::patch('admin/verificaciones-usuario/{id}', [VerificacionUsuarioController::class, 'update']);

            Route::get('admin/tipos-tarifa-servicio', [AdminTipoTarifaServicioController::class, 'index']);
            Route::get('admin/tipos-tarifa-servicio/{id}', [AdminTipoTarifaServicioController::class, 'show']);
            Route::post('admin/tipos-tarifa-servicio', [AdminTipoTarifaServicioController::class, 'store']);
            Route::put('admin/tipos-tarifa-servicio/{id}', [AdminTipoTarifaServicioController::class, 'update']);
            Route::patch('admin/tipos-tarifa-servicio/{id}/activar', [AdminTipoTarifaServicioController::class, 'activar']);
            Route::patch('admin/tipos-tarifa-servicio/{id}/desactivar', [AdminTipoTarifaServicioController::class, 'desactivar']);
        });

        Route::middleware('empresa.user')->group(function (): void {
            Route::middleware('modulo:informes')->group(function (): void {
                Route::apiResource('informes', InformeController::class);
            });

            Route::middleware('modulo:inventario')->group(function (): void {
                Route::apiResource('inventario-items', InventarioItemController::class);
                Route::apiResource('inventario-movimientos', InventarioMovimientoController::class)->only(['index', 'show', 'store']);
                Route::apiResource('inventario-ubicaciones', InventarioUbicacionController::class);
            });

            Route::middleware('modulo:calendario')->group(function (): void {
                Route::apiResource('calendario-eventos', CalendarioEventoController::class);
                Route::get('dashboard/calendario', [DashboardController::class, 'calendario']);
            });

            Route::get('dashboard/resumen', [DashboardController::class, 'resumen']);
            Route::get('dashboard/proximas-ordenes', [DashboardController::class, 'proximasOrdenes']);

            Route::middleware('modulo:ordenes')->group(function (): void {
                Route::get('ordenes-trabajo', [OrdenTrabajoController::class, 'index']);
                Route::post('ordenes-trabajo', [OrdenTrabajoController::class, 'store']);
                Route::get('ordenes-trabajo/{orden}', [OrdenTrabajoController::class, 'show']);
                Route::put('ordenes-trabajo/{orden}', [OrdenTrabajoController::class, 'update']);
                Route::post('ordenes-trabajo/{orden}/completar', [OrdenTrabajoController::class, 'completar']);
                Route::post('ordenes-trabajo/{orden}/cancelar', [OrdenTrabajoController::class, 'cancelar']);
                Route::post('ordenes-trabajo/{orden}/generar-factura', [OrdenTrabajoController::class, 'generarFactura']);
            });

            Route::middleware('modulo:facturacion')->group(function (): void {
                Route::get('facturas', [FacturaController::class, 'index']);
                Route::get('facturas/{factura}', [FacturaController::class, 'show']);
                Route::post('facturas/{factura}/marcar-pagada', [FacturaController::class, 'marcarPagada']);
                Route::post('facturas/{factura}/anular', [FacturaController::class, 'anular']);
                Route::post('facturas/{factura}/rectificar', [FacturaController::class, 'rectificar']);

                Route::get('registros-facturacion', [RegistroFacturacionController::class, 'index']);
                Route::get('registros-facturacion/exportar', [RegistroFacturacionController::class, 'exportar']);
                Route::get('registros-facturacion/validar-cadena', [RegistroFacturacionController::class, 'validarCadena']);
                Route::get('registros-facturacion/{id}', [RegistroFacturacionController::class, 'show']);

                Route::post('verifactu/enviar-pendientes', [VerifactuController::class, 'enviarPendientes']);
            });

            Route::middleware('modulo:clientes')->group(function (): void {
                Route::apiResource('clientes', ClienteController::class);
            });

            Route::middleware('modulo:servicios')->group(function (): void {
                Route::patch('servicios/{id}/activar', [ServicioController::class, 'activar']);
                Route::patch('servicios/{id}/desactivar', [ServicioController::class, 'desactivar']);
                Route::get('servicios/{servicio}/precios', [ServicioPrecioController::class, 'indexByServicio']);
                Route::post('servicios/{servicio}/precios', [ServicioPrecioController::class, 'storeForServicio']);
                Route::get('tipos-tarifa-servicio', [TipoTarifaServicioController::class, 'index']);
                Route::apiResource('servicios', ServicioController::class);
                Route::apiResource('servicio-precios', ServicioPrecioController::class);
            });

            Route::get('registros-evento', [RegistroEventoController::class, 'index']);
            Route::get('registros-evento/exportar', [RegistroEventoController::class, 'exportar']);
            Route::get('registros-evento-facturacion', [RegistroEventoFacturacionController::class, 'index']);
            Route::get('registros-evento-facturacion/exportar', [RegistroEventoFacturacionController::class, 'exportar']);

            Route::get('declaraciones-responsables-software', [DeclaracionResponsableSoftwareController::class, 'index']);
            Route::post('declaraciones-responsables-software', [DeclaracionResponsableSoftwareController::class, 'store']);
            Route::get('declaraciones-responsables-software/{id}', [DeclaracionResponsableSoftwareController::class, 'show']);

            Route::apiResource('tareas', TareaController::class);
        });

        Route::apiResource('tipos-cliente', TipoClienteController::class)->only(['index', 'show'])->parameters(['tipos-cliente' => 'id']);
        Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class)->only(['index', 'show'])->parameters(['tipos-evento-facturacion' => 'id']);
        Route::apiResource('tipos-factura', TipoFacturaController::class)->only(['index', 'show'])->parameters(['tipos-factura' => 'id']);
        Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class)->only(['index', 'show'])->parameters(['tipos-inventario-movimiento' => 'id']);
        Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class)->only(['index', 'show'])->parameters(['inventario-unidades-medida' => 'id']);
        Route::apiResource('tipos-localizacion-cliente', TipoLocalizacionClienteController::class)->only(['index', 'show'])->parameters(['tipos-localizacion-cliente' => 'id']);
        Route::apiResource('tipos-rectificacion', TipoRectificacionController::class)->only(['index', 'show'])->parameters(['tipos-rectificacion' => 'id']);
        Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class)->only(['index', 'show'])->parameters(['tipos-registro-facturacion' => 'id']);

        Route::get('verificaciones-usuario/me', [VerificacionUsuarioController::class, 'showMine']);
        Route::post('verificaciones-usuario', [VerificacionUsuarioController::class, 'store']);
    });
});

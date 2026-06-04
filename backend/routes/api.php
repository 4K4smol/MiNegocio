<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\AdminAuditoriaController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminUsuarioController;
use App\Http\Controllers\Api\V1\Admin\CatalogoController as AdminCatalogoController;
use App\Http\Controllers\Api\V1\Admin\DocumentoVerificacionController as AdminDocumentoVerificacionController;
use App\Http\Controllers\Api\V1\Admin\EmpresaController as AdminEmpresaController;
use App\Http\Controllers\Api\V1\Admin\SolicitudVerificacionController as AdminSolicitudVerificacionController;
use App\Http\Controllers\Api\V1\Admin\TipoTarifaServicioController as AdminTipoTarifaServicioController;
use App\Http\Controllers\Api\V1\AdminEmpresaModuloController;
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
use App\Http\Controllers\Api\V1\TipoClienteController;
use App\Http\Controllers\Api\V1\TipoDocumentoIdentidadController;
use App\Http\Controllers\Api\V1\TipoEmpresaController;
use App\Http\Controllers\Api\V1\TipoEventoFacturacionController;
use App\Http\Controllers\Api\V1\TipoFacturaController;
use App\Http\Controllers\Api\V1\TipoInventarioMovimientoController;
use App\Http\Controllers\Api\V1\TipoRectificacionController;
use App\Http\Controllers\Api\V1\TipoRegistroFacturacionController;
use App\Http\Controllers\Api\V1\TipoTarifaServicioController;
use App\Http\Controllers\Api\V1\VerifactuController;
use App\Http\Controllers\Api\V1\VerificacionUsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Rutas públicas
    |--------------------------------------------------------------------------
    */
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)
        ->only(['index', 'show'])
        ->parameters(['tipos-documento-identidad' => 'id']);

    Route::apiResource('tipos-empresa', TipoEmpresaController::class)
        ->only(['index', 'show'])
        ->parameters(['tipos-empresa' => 'id']);

    /*
    |--------------------------------------------------------------------------
    | Rutas autenticadas
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | Zona de administración global
        |--------------------------------------------------------------------------
        */
        Route::middleware('admin')->group(function (): void {
            Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);
            Route::get('admin/auditoria', [AdminAuditoriaController::class, 'index']);
            Route::get('admin/catalogos', [AdminCatalogoController::class, 'index']);

            // Usuarios
            Route::controller(AdminUsuarioController::class)->prefix('admin/usuarios')->group(function (): void {
                Route::get('', 'index');
                Route::get('{user}', 'show');
                Route::patch('{user}/activar', 'activar');
                Route::patch('{user}/desactivar', 'desactivar');
                Route::patch('{user}/rol', 'cambiarRol');
            });

            // Empresas
            Route::controller(AdminEmpresaController::class)->prefix('admin/empresas')->group(function (): void {
                Route::get('', 'index');
                Route::get('{empresa}', 'show');
                Route::patch('{empresa}/activar', 'activar');
                Route::patch('{empresa}/desactivar', 'desactivar');
            });

            // Módulos globales
            Route::apiResource('modulos', ModuloController::class);
            Route::patch('modulos/{id}/activar', [ModuloController::class, 'activar']);
            Route::patch('modulos/{id}/desactivar', [ModuloController::class, 'desactivar']);

            // Módulos asignados a empresas
            Route::controller(AdminEmpresaModuloController::class)->prefix('admin/empresas/{empresa}/modulos')->group(function (): void {
                Route::get('', 'index');
                Route::post('{modulo}/activar', 'activar');
                Route::post('{modulo}/desactivar', 'desactivar');
            });

            // Validación de solicitudes
            Route::controller(AdminSolicitudVerificacionController::class)->prefix('admin/solicitudes-verificacion')->group(function (): void {
                Route::get('', 'index');
                Route::get('{empresa}', 'show');

                Route::post('{empresa}/aprobar', 'aprobar');
                Route::post('{empresa}/rechazar', 'rechazar');
                Route::post('{empresa}/fases/identidad/aprobar', 'aprobarIdentidad');
                Route::post('{empresa}/fases/identidad/rechazar', 'rechazarIdentidad');
                Route::post('{empresa}/fases/empresa/aprobar', 'aprobarEmpresa');
                Route::post('{empresa}/fases/empresa/rechazar', 'rechazarEmpresa');
                Route::post('{empresa}/fases/representacion/aprobar', 'aprobarRepresentacion');
                Route::post('{empresa}/fases/representacion/rechazar', 'rechazarRepresentacion');
            });

            Route::get('admin/documentos-verificacion/{documento}/preview', [AdminDocumentoVerificacionController::class, 'preview']);

            Route::controller(VerificacionUsuarioController::class)->prefix('admin/verificaciones-usuario')->group(function (): void {
                Route::get('', 'index');
                Route::get('{id}', 'show');
                Route::patch('{id}', 'update');
            });

            // Catálogos administrables
            Route::apiResource('estados-factura', EstadoFacturaController::class)
                ->except(['index', 'show'])
                ->parameters(['estados-factura' => 'id']);

            Route::apiResource('roles', RoleController::class)->only(['index', 'show']);

            Route::apiResource('tipos-cliente', TipoClienteController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-cliente' => 'id']);
            Route::patch('tipos-cliente/{id}/activar', [TipoClienteController::class, 'activar']);
            Route::patch('tipos-cliente/{id}/desactivar', [TipoClienteController::class, 'desactivar']);

            Route::apiResource('tipos-empresa', TipoEmpresaController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-empresa' => 'id']);

            Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-documento-identidad' => 'id']);

            Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-evento-facturacion' => 'id']);

            Route::apiResource('tipos-factura', TipoFacturaController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-factura' => 'id']);

            Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-inventario-movimiento' => 'id']);

            Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class)
                ->except(['index', 'show'])
                ->parameters(['inventario-unidades-medida' => 'id']);

            Route::apiResource('tipos-rectificacion', TipoRectificacionController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-rectificacion' => 'id']);

            Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class)
                ->except(['index', 'show'])
                ->parameters(['tipos-registro-facturacion' => 'id']);

            Route::controller(AdminTipoTarifaServicioController::class)->prefix('admin/tipos-tarifa-servicio')->group(function (): void {
                Route::get('', 'index');
                Route::get('{id}', 'show');
                Route::post('', 'store');
                Route::put('{id}', 'update');
                Route::patch('{id}/activar', 'activar');
                Route::patch('{id}/desactivar', 'desactivar');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Zona privada de empresa
        |--------------------------------------------------------------------------
        */
        Route::middleware('empresa.user')->prefix('empresa')->group(function (): void {
            // Dashboard
            Route::get('dashboard/resumen', [DashboardController::class, 'resumen']);
            Route::get('dashboard/proximas-ordenes', [DashboardController::class, 'proximasOrdenes']);

            // Clientes
            Route::middleware('modulo:clientes')->group(function (): void {
                Route::apiResource('clientes', ClienteController::class);
            });

            // Servicios y precios
            Route::middleware('modulo:servicios')->group(function (): void {
                Route::patch('servicios/{id}/activar', [ServicioController::class, 'activar']);
                Route::patch('servicios/{id}/desactivar', [ServicioController::class, 'desactivar']);

                Route::get('servicios/{servicio}/precios', [ServicioPrecioController::class, 'indexByServicio']);
                Route::post('servicios/{servicio}/precios', [ServicioPrecioController::class, 'storeForServicio']);

                Route::apiResource('servicios', ServicioController::class);
                Route::apiResource('servicio-precios', ServicioPrecioController::class);
            });

            // Órdenes de trabajo
            Route::middleware('modulo:ordenes')->group(function (): void {
                Route::controller(OrdenTrabajoController::class)->prefix('ordenes-trabajo')->group(function (): void {
                    Route::get('', 'index');
                    Route::post('', 'store');
                    Route::get('{orden}', 'show');
                    Route::put('{orden}', 'update');
                    Route::post('{orden}/completar', 'completar');
                    Route::post('{orden}/cancelar', 'cancelar');
                    Route::post('{orden}/generar-factura', 'generarFactura');
                });
            });

            // Calendario
            Route::middleware('modulo:calendario')->group(function (): void {
                Route::apiResource('calendario-eventos', CalendarioEventoController::class);
                Route::get('dashboard/calendario', [DashboardController::class, 'calendario']);
            });

            // Facturación y VeriFactu
            Route::middleware('modulo:facturacion')->group(function (): void {
                Route::controller(FacturaController::class)->prefix('facturas')->group(function (): void {
                    Route::get('', 'index');
                    Route::post('', 'store');
                    Route::get('{factura}', 'show');
                    Route::put('{id}', 'update');
                    Route::delete('{id}', 'destroy');

                    Route::post('{factura}/emitir', 'emitir');
                    Route::get('{factura}/pdf', 'descargarPdf');
                    Route::post('{factura}/cobros', 'registrarCobro');
                    Route::post('{factura}/marcar-pagada', 'marcarPagada');
                    Route::post('{factura}/anular', 'anular');
                    Route::post('{factura}/rectificar', 'rectificar');
                    Route::get('{factura}/historial', 'historial');
                    Route::get('{factura}/registros-facturacion', 'registrosFacturacion');
                });

                Route::controller(RegistroFacturacionController::class)->prefix('registros-facturacion')->group(function (): void {
                    Route::get('', 'index');
                    Route::get('exportar', 'exportar');
                    Route::get('validar-cadena', 'validarCadena');
                    Route::get('{id}', 'show');
                });

                Route::get('verifactu/estado', [VerifactuController::class, 'estado']);
                Route::get('verifactu/configuracion', [VerifactuController::class, 'configuracion']);
                Route::get('verifactu/incidencias', [VerifactuController::class, 'incidencias']);
                Route::post('verifactu/enviar-pendientes', [VerifactuController::class, 'enviarPendientes']);
            });

            // Inventario
            Route::middleware('modulo:inventario')->group(function (): void {
                Route::apiResource('inventario-items', InventarioItemController::class)
                    ->parameters(['inventario-items' => 'id']);

                Route::apiResource('inventario-movimientos', InventarioMovimientoController::class)
                    ->only(['index', 'show', 'store']);

                Route::apiResource('inventario-ubicaciones', InventarioUbicacionController::class)
                    ->parameters(['inventario-ubicaciones' => 'id']);
            });

            // Informes
            Route::middleware('modulo:informes')->group(function (): void {
                Route::apiResource('informes', InformeController::class);
            });

            // Auditoría, eventos y declaraciones técnicas
            Route::get('registros-evento', [RegistroEventoController::class, 'index']);
            Route::get('registros-evento/exportar', [RegistroEventoController::class, 'exportar']);
            Route::get('registros-evento-facturacion', [RegistroEventoFacturacionController::class, 'index']);
            Route::get('registros-evento-facturacion/exportar', [RegistroEventoFacturacionController::class, 'exportar']);

            Route::controller(DeclaracionResponsableSoftwareController::class)->prefix('declaraciones-responsables-software')->group(function (): void {
                Route::get('', 'index');
                Route::post('', 'store');
                Route::get('{id}', 'show');
            });

            Route::apiResource('tareas', TareaController::class);
        });

        /*
        |--------------------------------------------------------------------------
        | Catálogos de consulta para usuarios autenticados
        |--------------------------------------------------------------------------
        */
        Route::apiResource('estados-factura', EstadoFacturaController::class)->only(['index', 'show']);

        Route::apiResource('tipos-cliente', TipoClienteController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-cliente' => 'id']);

        Route::apiResource('tipos-evento-facturacion', TipoEventoFacturacionController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-evento-facturacion' => 'id']);

        Route::apiResource('tipos-factura', TipoFacturaController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-factura' => 'id']);

        Route::apiResource('tipos-inventario-movimiento', TipoInventarioMovimientoController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-inventario-movimiento' => 'id']);

        Route::apiResource('inventario-unidades-medida', InventarioUnidadMedidaController::class)
            ->only(['index', 'show'])
            ->parameters(['inventario-unidades-medida' => 'id']);

        Route::apiResource('tipos-rectificacion', TipoRectificacionController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-rectificacion' => 'id']);

        Route::apiResource('tipos-registro-facturacion', TipoRegistroFacturacionController::class)
            ->only(['index', 'show'])
            ->parameters(['tipos-registro-facturacion' => 'id']);

        Route::get('tipos-tarifa-servicio', [TipoTarifaServicioController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Verificación del usuario autenticado
        |--------------------------------------------------------------------------
        */
        Route::get('verificaciones-usuario/me', [VerificacionUsuarioController::class, 'showMine']);
        Route::post('verificaciones-usuario', [VerificacionUsuarioController::class, 'store']);
    });
});

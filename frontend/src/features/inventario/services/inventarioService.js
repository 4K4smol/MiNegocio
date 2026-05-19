import { createCrudApi, endpoints } from "../../../shared/api";

export const inventarioItemsService = createCrudApi(endpoints.inventario.items);
export const inventarioUbicacionesService = createCrudApi(endpoints.inventario.ubicaciones);
export const inventarioUnidadesMedidaService = createCrudApi(endpoints.inventario.unidadesMedida);
export const inventarioMovimientosService = createCrudApi(endpoints.inventario.movimientos);
export const tiposInventarioMovimientoService = createCrudApi(endpoints.inventario.tiposMovimiento);

export const inventarioService = inventarioItemsService;

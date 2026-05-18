import { apiRequest, createCrudApi, endpoints } from "../../../shared/api";

export const inventarioItemsService = createCrudApi(endpoints.inventario.items);
const ubicacionesCrud = createCrudApi(endpoints.inventario.ubicaciones);
export const inventarioUbicacionesService = {
    ...ubicacionesCrud,
    activar: (id) => apiRequest(`${endpoints.inventario.ubicaciones}/${id}/activar`, { method: "PATCH" }),
    desactivar: (id) => apiRequest(`${endpoints.inventario.ubicaciones}/${id}/desactivar`, { method: "PATCH" }),
};
export const inventarioUnidadesMedidaService = createCrudApi(endpoints.inventario.unidadesMedida);
export const inventarioMovimientosService = createCrudApi(endpoints.inventario.movimientos);
export const tiposInventarioMovimientoService = createCrudApi(endpoints.inventario.tiposMovimiento);

export const inventarioService = inventarioItemsService;

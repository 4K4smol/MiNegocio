import { apiRequest, createCrudApi, endpoints } from "../../../shared/api";

const crudApi = createCrudApi(endpoints.ordenesTrabajo);

export const ordenesTrabajoService = {
    ...crudApi,
    completar: (id) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/completar`, {
            method: "POST",
        }),
    cancelar: (id) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/cancelar`, {
            method: "POST",
        }),
    generarFactura: (id) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/generar-factura`, {
            method: "POST",
        }),
};

import { apiRequest, endpoints } from "../../../shared/api";

export const ordenesApi = {
    list: (params) => apiRequest(endpoints.ordenesTrabajo, { params }),
    get: (id) => apiRequest(`${endpoints.ordenesTrabajo}/${id}`),
    create: (payload) =>
        apiRequest(endpoints.ordenesTrabajo, {
            method: "POST",
            body: payload,
        }),
    update: (id, payload) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}`, {
            method: "PUT",
            body: payload,
        }),
    completar: (id) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/completar`, {
            method: "POST",
        }),
    cancelar: (id) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/cancelar`, {
            method: "POST",
        }),
    generarFactura: (id, body) =>
        apiRequest(`${endpoints.ordenesTrabajo}/${id}/generar-factura`, {
            method: "POST",
            body,
        }),
};


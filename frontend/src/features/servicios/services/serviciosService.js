import { apiRequest, createCrudApi, endpoints } from "../../../shared/api";

const unwrap = (response) => response?.data ?? response;

export const serviciosService = {
    ...createCrudApi(endpoints.servicios),
    activar: (id) =>
        apiRequest(`${endpoints.servicios}/${id}/activar`, {
            method: "PATCH",
        }),
    desactivar: (id) =>
        apiRequest(`${endpoints.servicios}/${id}/desactivar`, {
            method: "PATCH",
        }),
};

export const tiposTarifaServicioService = {
    list: () => apiRequest(endpoints.tiposTarifaServicio).then(unwrap),
};

export const servicioPreciosService = {
    ...createCrudApi(endpoints.servicioPrecios),
    listByServicio: (servicioId) =>
        apiRequest(`${endpoints.servicios}/${servicioId}/precios`),
    createForServicio: (servicioId, payload) =>
        apiRequest(`${endpoints.servicios}/${servicioId}/precios`, {
            method: "POST",
            body: payload,
        }),
};
import { apiRequest, createCrudApi, endpoints } from "../../../shared/api";

export const serviciosService = {
    ...createCrudApi(endpoints.servicios),
    activar: (id) => apiRequest(`${endpoints.servicios}/${id}/activar`, { method: "PATCH" }),
    desactivar: (id) => apiRequest(`${endpoints.servicios}/${id}/desactivar`, { method: "PATCH" }),
};

export const servicioTarifasService = {
    ...createCrudApi(endpoints.servicioTarifas),
    activar: (id) => apiRequest(`${endpoints.servicioTarifas}/${id}/activar`, { method: "PATCH" }),
    desactivar: (id) => apiRequest(`${endpoints.servicioTarifas}/${id}/desactivar`, { method: "PATCH" }),
    marcarDefault: (id) => apiRequest(`${endpoints.servicioTarifas}/${id}/default`, { method: "PATCH" }),
};

export const servicioPreciosService = {
    ...createCrudApi(endpoints.servicioPrecios),
    listByServicio: (servicioId) => apiRequest(`${endpoints.servicios}/${servicioId}/precios`),
    createForServicio: (servicioId, payload) =>
        apiRequest(`${endpoints.servicios}/${servicioId}/precios`, {
            method: "POST",
            body: payload,
        }),
};

export const servicioCategoriasLogicasService = {
    list: () => apiRequest(endpoints.servicioCategoriasLogicas),
    renombrar: (payload) =>
        apiRequest(`${endpoints.servicioCategoriasLogicas}/renombrar`, {
            method: "PATCH",
            body: payload,
        }),
    fusionar: (payload) =>
        apiRequest(`${endpoints.servicioCategoriasLogicas}/fusionar`, {
            method: "PATCH",
            body: payload,
        }),
    vaciar: (payload) =>
        apiRequest(`${endpoints.servicioCategoriasLogicas}/vaciar`, {
            method: "PATCH",
            body: payload,
        }),
};

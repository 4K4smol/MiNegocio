import { apiRequest, endpoints } from '../../../shared/api'

export const facturasService = {
    list: (params) => apiRequest(endpoints.facturas, { params }),
    get: (id) => apiRequest(`${endpoints.facturas}/${id}`),
    create: (data) => apiRequest(endpoints.facturas, { method: 'POST', body: data }),
    update: (id, data) => apiRequest(`${endpoints.facturas}/${id}`, { method: 'PUT', body: data }),
    emitir: (id) => apiRequest(`${endpoints.facturas}/${id}/emitir`, { method: 'POST' }),
    marcarPagada: (id) => apiRequest(`${endpoints.facturas}/${id}/marcar-pagada`, { method: 'POST' }),
    anular: (id, motivoAnulacion) =>
        apiRequest(`${endpoints.facturas}/${id}/anular`, {
            method: 'POST',
            body: { motivo_anulacion: motivoAnulacion },
        }),
    rectificar: (id, motivoRectificacion) =>
        apiRequest(`${endpoints.facturas}/${id}/rectificar`, {
            method: 'POST',
            body: { motivo_rectificacion: motivoRectificacion },
        }),
}

export const facturasApi = facturasService

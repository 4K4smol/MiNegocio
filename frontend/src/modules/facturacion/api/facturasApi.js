import { apiRequest } from '../../../shared/api'

export const facturasApi = {
    list: (params) => apiRequest('facturas', { params }),
    get: (id) => apiRequest(`facturas/${id}`),
    marcarPagada: (id) =>
        apiRequest(`facturas/${id}/marcar-pagada`, {
            method: 'POST',
        }),
    anular: (id, motivoAnulacion) =>
        apiRequest(`facturas/${id}/anular`, {
            method: 'POST',
            body: {
                motivo_anulacion: motivoAnulacion,
            },
        }),
    rectificar: (id, motivoRectificacion) =>
        apiRequest(`facturas/${id}/rectificar`, {
            method: 'POST',
            body: {
                motivo_rectificacion: motivoRectificacion,
            },
        }),
}


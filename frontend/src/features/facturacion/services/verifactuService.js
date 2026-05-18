import { apiRequest, endpoints } from '../../../shared/api'

export const verifactuService = {
    getEstado: () => apiRequest(`${endpoints.verifactu}/estado`),
    getConfiguracion: () => apiRequest(`${endpoints.verifactu}/configuracion`),
    enviarPendientes: () =>
        apiRequest(`${endpoints.verifactu}/enviar-pendientes`, { method: 'POST' }),
    getIncidencias: (params) => apiRequest(`${endpoints.verifactu}/incidencias`, { params }),
}

export const verifactuApi = verifactuService

import { apiRequest, endpoints } from '../../../shared/api'

export const verifactuService = {
    enviarPendientes: () =>
        apiRequest(`${endpoints.verifactu}/enviar-pendientes`, {
            method: 'POST',
        }),
}

export const verifactuApi = verifactuService

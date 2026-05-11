import { apiRequest } from '../../../shared/api'

export const verifactuApi = {
    enviarPendientes: () =>
        apiRequest('verifactu/enviar-pendientes', {
            method: 'POST',
        }),
}


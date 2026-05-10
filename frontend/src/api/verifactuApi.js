import { apiRequest } from './httpClient'

export const registrosFacturacionApi = {
    list: (params) => apiRequest('registros-facturacion', { params }),
    get: (id) => apiRequest(`registros-facturacion/${id}`),
    validarCadena: () => apiRequest('registros-facturacion/validar-cadena'),
    exportar: () => apiRequest('registros-facturacion/exportar'),
}

export const verifactuApi = {
    registrosFacturacionApi,
    enviarPendientes: () =>
        apiRequest('verifactu/enviar-pendientes', {
            method: 'POST',
        }),
}


import { apiRequest, endpoints } from '../../../shared/api'

export const registrosFacturacionService = {
    list: (params) => apiRequest(endpoints.registrosFacturacion, { params }),
    get: (id) => apiRequest(`${endpoints.registrosFacturacion}/${id}`),
    validarCadena: () => apiRequest(`${endpoints.registrosFacturacion}/validar-cadena`),
    exportar: () => apiRequest(`${endpoints.registrosFacturacion}/exportar`),
}

export const registrosFacturacionApi = registrosFacturacionService

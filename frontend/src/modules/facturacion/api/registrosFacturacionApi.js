import { apiRequest } from '../../../shared/api'

export const registrosFacturacionApi = {
    list: (params) => apiRequest('registros-facturacion', { params }),
    get: (id) => apiRequest(`registros-facturacion/${id}`),
    validarCadena: () => apiRequest('registros-facturacion/validar-cadena'),
    exportar: () => apiRequest('registros-facturacion/exportar'),
}


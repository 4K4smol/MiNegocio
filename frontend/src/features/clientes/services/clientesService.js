import { apiRequest, createCrudApi, endpoints } from '../../../shared/api'

export const clientesService = {
    ...createCrudApi(endpoints.clientes),
    getTiposCliente: (params) => apiRequest(endpoints.catalogos.tiposCliente, { params }),
}
export const clientesApi = clientesService

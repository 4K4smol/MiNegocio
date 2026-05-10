import { createCrudApi } from './crudApi'
import { apiRequest } from './httpClient'

export const ordenesApi = {
    ...createCrudApi('ordenes-trabajo'),
    completar: (id) =>
        apiRequest(`ordenes-trabajo/${id}/completar`, {
            method: 'POST',
        }),
    cancelar: (id) =>
        apiRequest(`ordenes-trabajo/${id}/cancelar`, {
            method: 'POST',
        }),
    generarFactura: (id) =>
        apiRequest(`ordenes-trabajo/${id}/generar-factura`, {
            method: 'POST',
        }),
}


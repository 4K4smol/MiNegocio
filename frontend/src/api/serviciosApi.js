import { createCrudApi } from './crudApi'

export const serviciosApi = createCrudApi('servicios')
export const servicioPreciosApi = createCrudApi('servicio-precios')
export const servicioTarifasApi = createCrudApi('servicio-tarifas')


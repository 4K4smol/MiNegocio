import { createCrudApi } from './crudApi'

export const tiposEmpresaApi = createCrudApi('tipos-empresa')
export const tiposDocumentoIdentidadApi = createCrudApi(
    'tipos-documento-identidad',
)
export const tiposClienteApi = createCrudApi('tipos-cliente')
export const tiposFacturaApi = createCrudApi('tipos-factura')
export const estadosFacturaApi = createCrudApi('estados-factura')
export const tiposRectificacionApi = createCrudApi('tipos-rectificacion')

export const catalogosApi = {
    tiposEmpresaApi,
    tiposDocumentoIdentidadApi,
    tiposClienteApi,
    tiposFacturaApi,
    estadosFacturaApi,
    tiposRectificacionApi,
}


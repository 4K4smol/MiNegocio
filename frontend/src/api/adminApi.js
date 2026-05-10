import { API_BASE_URL } from './apiConfig'
import { apiRequest } from './httpClient'

const buildAdminUrl = (path) =>
    `${API_BASE_URL.replace(/\/+$/, '')}/${String(path).replace(/^\/+/, '')}`

export const adminSolicitudesApi = {
    list: (params) => apiRequest('admin/solicitudes-registro', { params }),
    get: (id) => apiRequest(`admin/solicitudes-registro/${id}`),
    aprobar: (id) =>
        apiRequest(`admin/solicitudes-registro/${id}/aprobar`, {
            method: 'POST',
        }),
    rechazar: (id, motivoRechazo) =>
        apiRequest(`admin/solicitudes-registro/${id}/rechazar`, {
            method: 'POST',
            body: {
                motivo_rechazo: motivoRechazo,
            },
        }),
    documentoUrl: (documentoId) =>
        buildAdminUrl(`admin/documentos-verificacion/${documentoId}/ver`),
}

export const adminEmpresaModulosApi = {
    list: (empresaId) => apiRequest(`admin/empresas/${empresaId}/modulos`),
    activar: (empresaId, moduloId) =>
        apiRequest(`admin/empresas/${empresaId}/modulos/${moduloId}/activar`, {
            method: 'POST',
        }),
    desactivar: (empresaId, moduloId) =>
        apiRequest(`admin/empresas/${empresaId}/modulos/${moduloId}/desactivar`, {
            method: 'POST',
        }),
}

export const adminApi = {
    adminSolicitudesApi,
    adminEmpresaModulosApi,
}


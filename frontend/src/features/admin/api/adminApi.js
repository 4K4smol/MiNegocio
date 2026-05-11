import { apiRequest, buildApiUrl, getToken } from "../../../shared/api";

const unwrap = (response) => response?.data ?? response;
const paginated = async (promise) => {
    const payload = unwrap(await promise);
    return {
        items: payload?.data || [],
        meta: {
            currentPage: payload?.current_page,
            lastPage: payload?.last_page,
            total: payload?.total,
            perPage: payload?.per_page,
        },
    };
};

export const previewDocumento = async (documentoId) => {
    const response = await fetch(buildApiUrl(`admin/documentos-verificacion/${documentoId}/preview`), {
        headers: {
            Accept: "application/pdf,image/*,*/*",
            Authorization: `Bearer ${getToken()}`,
        },
    });

    if (!response.ok) {
        let message = "No se ha podido abrir el documento.";
        try {
            const payload = await response.json();
            message = payload?.message || message;
        } catch {
            // Binary endpoint.
        }
        throw new Error(message);
    }

    const blob = await response.blob();
    const disposition = response.headers.get("Content-Disposition") || "";
    const filename = disposition.match(/filename="?([^"]+)"?/i)?.[1] || "documento";
    const objectUrl = URL.createObjectURL(blob);

    return {
        objectUrl,
        url: objectUrl,
        mimeType: response.headers.get("Content-Type") || blob.type || "application/octet-stream",
        filename,
    };
};

export const adminApi = {
    getAdminDashboard: () => apiRequest("admin/dashboard"),

    getSolicitudesVerificacion: (params) => paginated(apiRequest("admin/solicitudes-verificacion", { params })),
    getSolicitudVerificacionDetalle: (empresaId) => apiRequest(`admin/solicitudes-verificacion/${empresaId}`).then(unwrap),
    aprobarSolicitud: (empresaId, data) => apiRequest(`admin/solicitudes-verificacion/${empresaId}/aprobar`, { method: "POST", body: data }),
    rechazarSolicitud: (empresaId, data) => apiRequest(`admin/solicitudes-verificacion/${empresaId}/rechazar`, { method: "POST", body: data }),
    solicitarSubsanacion: (empresaId, data) =>
        apiRequest(`admin/solicitudes-verificacion/${empresaId}/solicitar-subsanacion`, { method: "POST", body: data }),
    previewDocumento,

    getAdminUsuarios: (params) => paginated(apiRequest("admin/usuarios", { params })),
    getAdminUsuarioDetalle: (userId) => apiRequest(`admin/usuarios/${userId}`).then(unwrap),
    activarUsuario: (userId) => apiRequest(`admin/usuarios/${userId}/activar`, { method: "PATCH" }),
    desactivarUsuario: (userId) => apiRequest(`admin/usuarios/${userId}/desactivar`, { method: "PATCH" }),
    cambiarRolUsuario: (userId, roleId) => apiRequest(`admin/usuarios/${userId}/rol`, { method: "PATCH", body: { role_id: roleId } }),

    getAdminEmpresas: (params) => paginated(apiRequest("admin/empresas", { params })),
    getAdminEmpresaDetalle: (empresaId) => apiRequest(`admin/empresas/${empresaId}`).then(unwrap),
    activarEmpresa: (empresaId) => apiRequest(`admin/empresas/${empresaId}/activar`, { method: "PATCH" }),
    desactivarEmpresa: (empresaId) => apiRequest(`admin/empresas/${empresaId}/desactivar`, { method: "PATCH" }),
    getEmpresaModulos: (empresaId) => apiRequest(`admin/empresas/${empresaId}/modulos`).then(unwrap),
    activarModuloEmpresa: (empresaId, moduloId) => apiRequest(`admin/empresas/${empresaId}/modulos/${moduloId}/activar`, { method: "POST" }),
    desactivarModuloEmpresa: (empresaId, moduloId) => apiRequest(`admin/empresas/${empresaId}/modulos/${moduloId}/desactivar`, { method: "POST" }),

    getAdminAuditoria: (params) => paginated(apiRequest("admin/auditoria", { params })),

    getAdminCatalogos: () => apiRequest("admin/catalogos").then(unwrap),
    getAdminModulos: () => apiRequest("modulos").then((response) => unwrap(response)?.data || unwrap(response) || []),
    getAdminRoles: (params) => paginated(apiRequest("roles", { params })),
    crearModulo: (data) => apiRequest("modulos", { method: "POST", body: data }),
    actualizarModulo: (id, data) => apiRequest(`modulos/${id}`, { method: "PUT", body: data }),
    activarModulo: (id) => apiRequest(`modulos/${id}/activar`, { method: "PATCH" }),
    desactivarModulo: (id) => apiRequest(`modulos/${id}/desactivar`, { method: "PATCH" }),

    // Compatibilidad con el servicio legacy mientras se retiran pantallas antiguas.
    getSolicitudes: (params) => apiRequest("admin/solicitudes", { params }),
    getSolicitud: (id) => apiRequest(`admin/solicitudes/${id}`),
    getEmpresas: (params) => apiRequest("admin/empresas", { params }),
    getEmpresa: (id) => apiRequest(`admin/empresas/${id}`),
    getCatalogos: () => apiRequest("admin/catalogos"),
    getDocumentoUrl: (id) => buildApiUrl(`admin/documentos/${id}/descargar`),
};

export const downloadAdminDocumento = async (id, filename = "documento") => {
    const response = await fetch(adminApi.getDocumentoUrl(id), {
        headers: {
            Accept: "application/octet-stream",
            Authorization: `Bearer ${getToken()}`,
        },
    });

    if (!response.ok) throw new Error("No se ha podido descargar el documento.");

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
};

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
    aprobarFaseSolicitud: (empresaId, fase) =>
        apiRequest(`admin/solicitudes-verificacion/${empresaId}/fases/${fase}/aprobar`, { method: "POST" }),
    rechazarFaseSolicitud: (empresaId, fase, data) =>
        apiRequest(`admin/solicitudes-verificacion/${empresaId}/fases/${fase}/rechazar`, { method: "POST", body: data }),
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
    getTiposCliente: (params) => paginated(apiRequest("tipos-cliente", { params })),
    crearTipoCliente: (data) => apiRequest("tipos-cliente", { method: "POST", body: data }),
    actualizarTipoCliente: (id, data) => apiRequest(`tipos-cliente/${id}`, { method: "PUT", body: data }),
    activarTipoCliente: (id) => apiRequest(`tipos-cliente/${id}/activar`, { method: "PATCH" }),
    desactivarTipoCliente: (id) => apiRequest(`tipos-cliente/${id}/desactivar`, { method: "PATCH" }),
    getTiposEmpresa: (params) => paginated(apiRequest("tipos-empresa", { params })),
    crearTipoEmpresa: (data) => apiRequest("tipos-empresa", { method: "POST", body: data }),
    actualizarTipoEmpresa: (id, data) => apiRequest(`tipos-empresa/${id}`, { method: "PUT", body: data }),
    getTiposDocumentoIdentidad: (params) => paginated(apiRequest("tipos-documento-identidad", { params })),
    crearTipoDocumentoIdentidad: (data) => apiRequest("tipos-documento-identidad", { method: "POST", body: data }),
    actualizarTipoDocumentoIdentidad: (id, data) => apiRequest(`tipos-documento-identidad/${id}`, { method: "PUT", body: data }),
    getInventarioUnidadesMedida: (params) => paginated(apiRequest("inventario-unidades-medida", { params })),
    crearInventarioUnidadMedida: (data) => apiRequest("inventario-unidades-medida", { method: "POST", body: data }),
    actualizarInventarioUnidadMedida: (id, data) => apiRequest(`inventario-unidades-medida/${id}`, { method: "PUT", body: data }),
    getTiposInventarioMovimiento: (params) => paginated(apiRequest("tipos-inventario-movimiento", { params })),
    crearTipoInventarioMovimiento: (data) => apiRequest("tipos-inventario-movimiento", { method: "POST", body: data }),
    actualizarTipoInventarioMovimiento: (id, data) => apiRequest(`tipos-inventario-movimiento/${id}`, { method: "PUT", body: data }),
    getTiposEventoFacturacion: (params) => paginated(apiRequest("tipos-evento-facturacion", { params })),
    crearTipoEventoFacturacion: (data) => apiRequest("tipos-evento-facturacion", { method: "POST", body: data }),
    actualizarTipoEventoFacturacion: (id, data) => apiRequest(`tipos-evento-facturacion/${id}`, { method: "PUT", body: data }),
    getTiposFactura: (params) => paginated(apiRequest("tipos-factura", { params })),
    crearTipoFactura: (data) => apiRequest("tipos-factura", { method: "POST", body: data }),
    actualizarTipoFactura: (id, data) => apiRequest(`tipos-factura/${id}`, { method: "PUT", body: data }),
    getTiposRectificacion: (params) => paginated(apiRequest("tipos-rectificacion", { params })),
    crearTipoRectificacion: (data) => apiRequest("tipos-rectificacion", { method: "POST", body: data }),
    actualizarTipoRectificacion: (id, data) => apiRequest(`tipos-rectificacion/${id}`, { method: "PUT", body: data }),
    getTiposRegistroFacturacion: (params) => paginated(apiRequest("tipos-registro-facturacion", { params })),
    crearTipoRegistroFacturacion: (data) => apiRequest("tipos-registro-facturacion", { method: "POST", body: data }),
    actualizarTipoRegistroFacturacion: (id, data) => apiRequest(`tipos-registro-facturacion/${id}`, { method: "PUT", body: data }),

    getAdminTiposTarifaServicio: () => apiRequest("admin/tipos-tarifa-servicio").then(unwrap),
    crearAdminTipoTarifaServicio: (data) => apiRequest("admin/tipos-tarifa-servicio", { method: "POST", body: data }).then(unwrap),
    actualizarAdminTipoTarifaServicio: (id, data) => apiRequest(`admin/tipos-tarifa-servicio/${id}`, { method: "PUT", body: data }).then(unwrap),
    activarAdminTipoTarifaServicio: (id) => apiRequest(`admin/tipos-tarifa-servicio/${id}/activar`, { method: "PATCH" }).then(unwrap),
    desactivarAdminTipoTarifaServicio: (id) => apiRequest(`admin/tipos-tarifa-servicio/${id}/desactivar`, { method: "PATCH" }).then(unwrap),
};

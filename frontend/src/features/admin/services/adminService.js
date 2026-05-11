import { adminApi, downloadAdminDocumento } from "../api/adminApi";

const unwrap = (response) => response?.data ?? response;
const collection = (response) => {
    const data = unwrap(response);
    return Array.isArray(data) ? data : data?.data || [];
};

const titleCaseEstado = (value = "pendiente") =>
    String(value)
        .replaceAll("_", " ")
        .replace(/^\w/, (letter) => letter.toUpperCase());

const normalizeSolicitud = (solicitud) => ({
    ...solicitud,
    estado: titleCaseEstado(solicitud.estado_verificacion),
    tipoEmpresa: solicitud.tipo_empresa || solicitud.empresa?.tipo_empresa || "No indicado",
    fecha: solicitud.created_at?.slice(0, 10) || "",
    responsable: {
        nombre: [
            solicitud.responsable?.nombre,
            solicitud.responsable?.apellido1,
            solicitud.responsable?.apellido2,
        ].filter(Boolean).join(" ") || "No indicado",
        email: solicitud.responsable?.email || "",
        telefono: solicitud.responsable?.telefono || "",
        tipoDocumento: "Documento identidad",
        numeroDocumento: "No disponible",
    },
    empresa: {
        nombreFiscal: solicitud.empresa?.nombre_fiscal || solicitud.nombre_fiscal || "",
        nombreComercial: solicitud.empresa?.nombre_comercial || solicitud.nombre_comercial || solicitud.nombre_fiscal || "",
        nif: solicitud.empresa?.nif || solicitud.nif || "",
        direccionFiscal: solicitud.empresa?.direccion_fiscal || "",
        email: solicitud.empresa?.correo || "",
        telefono: solicitud.empresa?.telefono || "",
    },
    documentos: (solicitud.documentos || []).map((documento) => ({
        ...documento,
        nombre: documento.nombre_original || documento.tipo_documento,
        descripcion: documento.tipo_documento,
        estado: "Pendiente",
    })),
    historial: solicitud.historial || [],
});

const normalizeEmpresa = (empresa) => ({
    ...empresa,
    nombre: empresa.nombre_comercial || empresa.nombre_fiscal,
    nif: empresa.nif,
    tipo: empresa.tipo_empresa?.nombre || empresa.tipo_empresa || "No indicado",
    estado: empresa.activa ? "Activa" : "Inactiva",
    fechaAlta: empresa.created_at?.slice(0, 10) || "",
    modulosActivos: empresa.modulos?.map((modulo) => modulo.nombre) || [],
    ultimaActividad: empresa.updated_at?.slice(0, 10) || "Sin actividad",
    usuarios: empresa.usuarios?.map((usuario) => usuario.nombre || usuario.name || usuario.email) || [],
});

export const adminService = {
    getDashboardStats: async () => {
        const data = unwrap(await adminApi.getAdminDashboard());
        return {
            metrics: {
                totalEmpresas: data.total_empresas,
                pendientes: data.empresas_pendientes,
                aprobadas: data.empresas_aprobadas,
                rechazadas: data.empresas_rechazadas,
                documentosPendientes: data.documentos_pendientes,
                usuariosPendientes: data.usuarios_pendientes,
            },
            ultimasSolicitudes: (data.solicitudes_recientes || []).map(normalizeSolicitud),
            actividadReciente: data.alertas || [],
        };
    },
    getSolicitudes: async (params) => collection(await adminApi.getSolicitudes(params)).map(normalizeSolicitud),
    getSolicitudById: async (id) => normalizeSolicitud(unwrap(await adminApi.getSolicitud(id))),
    aprobarIdentidad: (id) => adminApi.aprobarIdentidad(id),
    rechazarIdentidad: (id, motivo) => adminApi.rechazarIdentidad(id, motivo),
    aprobarEmpresa: (id) => adminApi.aprobarEmpresa(id),
    rechazarEmpresa: (id, motivo) => adminApi.rechazarEmpresa(id, motivo),
    aprobarRepresentacion: (id) => adminApi.aprobarRepresentacion(id),
    rechazarRepresentacion: (id, motivo) => adminApi.rechazarRepresentacion(id, motivo),
    aprobarSolicitudTotal: (id) => adminApi.aprobarSolicitudTotal(id),
    rechazarSolicitudTotal: (id, motivo) => adminApi.rechazarSolicitudTotal(id, motivo),
    getEmpresas: async (params) => collection(await adminApi.getEmpresas(params)).map(normalizeEmpresa),
    getEmpresaById: async (id) => normalizeEmpresa(unwrap(await adminApi.getEmpresa(id))),
    activarEmpresa: (id) => adminApi.activarEmpresa(id),
    desactivarEmpresa: (id) => adminApi.desactivarEmpresa(id),
    getCatalogos: async () => {
        const data = unwrap(await adminApi.getCatalogos());
        return Object.fromEntries(
            Object.entries(data)
                .filter(([, value]) => Array.isArray(value))
                .map(([key, value]) => [
                    key.replaceAll("_", " "),
                    value.map((item) => item.nombre || item.descripcion || item.slug || String(item.id)),
                ]),
        );
    },
    descargarDocumento: downloadAdminDocumento,
};

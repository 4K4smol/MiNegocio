import { adminApi, previewDocumento } from "./adminApi";

export const adminSolicitudesApi = {
    listar: (params = {}) => adminApi.getSolicitudesVerificacion(params),
    detalle: (empresaId) => adminApi.getSolicitudVerificacionDetalle(empresaId),
    aprobar: (empresaId, body) => adminApi.aprobarSolicitud(empresaId, body),
    rechazar: (empresaId, body) => adminApi.rechazarSolicitud(empresaId, body),
    solicitarSubsanacion: (empresaId, body) => adminApi.solicitarSubsanacion(empresaId, body),
};

export const previewDocumentoVerificacion = previewDocumento;

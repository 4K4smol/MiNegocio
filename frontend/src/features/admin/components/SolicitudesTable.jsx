import { EstadoSolicitudBadge } from "./EstadoSolicitudBadge";
import { AdminActionsMenu } from "./AdminActionsMenu";

const formatDate = (value) => {
    if (!value) return "Sin fecha";
    return new Intl.DateTimeFormat("es-ES", { dateStyle: "medium" }).format(new Date(value));
};

export function SolicitudesTable({ solicitudes = [], selectedId, onSelect, onDecision }) {
    const getEmpresaNombre = (solicitud) =>
        solicitud.empresa?.nombre_fiscal || solicitud.empresa?.nombreFiscal || solicitud.nombre_fiscal || "Sin nombre fiscal";
    const getEmpresaComercial = (solicitud) =>
        solicitud.empresa?.nombre_comercial || solicitud.empresa?.nombreComercial || solicitud.nombre_comercial || "Sin nombre comercial";
    const getNif = (solicitud) => solicitud.empresa?.nif || solicitud.nif || "No indicado";
    const getResponsable = (solicitud) =>
        solicitud.responsable?.nombre ||
        [solicitud.responsable?.nombre, solicitud.responsable?.apellido1].filter(Boolean).join(" ") ||
        "No indicado";
    const getEmail = (solicitud) => solicitud.responsable?.email || "No indicado";
    const getTipo = (solicitud) => solicitud.empresa?.tipo_empresa || solicitud.tipoEmpresa || "No indicado";
    const getEstado = (solicitud) => solicitud.estado_verificacion || solicitud.estado || "pendiente";
    const getFecha = (solicitud) => solicitud.fecha_solicitud || solicitud.fecha || solicitud.created_at;
    const can = (solicitud, action) =>
        !Array.isArray(solicitud.acciones_disponibles) || solicitud.acciones_disponibles.includes(action);

    return (
        <section className="admin-card solicitudes-table-card">
            <div className="admin-table-wrap">
                <table className="admin-table solicitudes-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>NIF</th>
                            <th>Responsable</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Docs</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {solicitudes.map((solicitud) => (
                            <tr key={solicitud.id} className={selectedId === solicitud.id ? "is-selected" : ""}>
                                <td data-label="Empresa">
                                    <strong>{getEmpresaNombre(solicitud)}</strong>
                                    <small>{getEmpresaComercial(solicitud)}</small>
                                </td>
                                <td data-label="NIF">{getNif(solicitud)}</td>
                                <td data-label="Responsable">
                                    <strong>{getResponsable(solicitud)}</strong>
                                </td>
                                <td data-label="Email">{getEmail(solicitud)}</td>
                                <td data-label="Tipo">{getTipo(solicitud)}</td>
                                <td data-label="Estado"><EstadoSolicitudBadge estado={getEstado(solicitud)} /></td>
                                <td data-label="Docs">{solicitud.documentos_count ?? 0}</td>
                                <td data-label="Fecha">{formatDate(getFecha(solicitud))}</td>
                                <td data-label="Acciones">
                                    <AdminActionsMenu
                                        actions={[
                                            { label: "Ver expediente", onClick: () => onSelect?.(solicitud.id), variant: "primary" },
                                            { label: "Aprobar", onClick: () => onDecision?.("aprobar", solicitud.id), variant: "success", hidden: !onDecision || !can(solicitud, "aprobar") },
                                            { label: "Rechazar", onClick: () => onDecision?.("rechazar", solicitud.id), variant: "danger", hidden: !onDecision || !can(solicitud, "rechazar") },
                                        ]}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

import { AdminActionsMenu } from "./AdminActionsMenu";
import { EstadoSolicitudBadge } from "./EstadoSolicitudBadge";

const GROUP_LABELS = {
    identidad: "Identidad",
    empresa_actividad: "Empresa / actividad",
    representacion: "Representación",
    otros: "Otros",
};

const formatBytes = (value) => {
    if (!value) return "No indicado";
    if (value < 1024) return `${value} B`;
    if (value < 1024 * 1024) return `${(value / 1024).toFixed(1)} KB`;
    return `${(value / 1024 / 1024).toFixed(1)} MB`;
};

const formatDateTime = (value) => {
    if (!value) return "Sin fecha";
    return new Intl.DateTimeFormat("es-ES", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
};

const PHASES = [
    { id: "identidad", label: "Identidad", stateKey: "estado_identidad" },
    { id: "empresa", label: "Empresa / actividad", stateKey: "estado_empresa" },
    { id: "representacion", label: "Representacion", stateKey: "estado_representacion" },
];

const normalize = (value) =>
    String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase();

function DataList({ items }) {
    return (
        <dl className="admin-data-list">
            {items.map(([label, value]) => (
                <div key={label}>
                    <dt>{label}</dt>
                    <dd>{value || "No indicado"}</dd>
                </div>
            ))}
        </dl>
    );
}

export function SolicitudDetallePanel({ solicitud, loading, onPreviewDocument, onDecision, onPhaseDecision }) {
    if (loading) return <section className="admin-card"><p className="admin-loading">Cargando expediente...</p></section>;
    if (!solicitud) return <section className="admin-card admin-empty-inline">Selecciona una solicitud para revisar el expediente.</section>;

    const empresa = solicitud.empresa || {};
    const responsable = solicitud.responsable || {};
    const documentos = solicitud.documentos || {};
    const historial = solicitud.historial || [];
    const estadoGlobal = solicitud.estado_actual || solicitud.estado_verificacion;
    const solicitudCerrada = ["aprobada", "rechazada"].includes(estadoGlobal);
    const representacionNoAplica = solicitud.estado_representacion === null && normalize(empresa.tipo_empresa) === "autonomo";
    const puedeAprobarGlobal = solicitud.estado_identidad === "aprobada"
        && solicitud.estado_empresa === "aprobada"
        && (representacionNoAplica || solicitud.estado_representacion === "aprobada");

    return (
        <section className="solicitud-detail-panel">
            <div className="detail-heading admin-card">
                <div>
                    <span className="admin-kicker">Expediente #{solicitud.id}</span>
                    <h2>{empresa.nombre_fiscal || "Solicitud de registro"}</h2>
                    <p>{empresa.nif || "NIF no indicado"}</p>
                </div>
                <EstadoSolicitudBadge estado={estadoGlobal} />
            </div>

            <div className="admin-detail-grid">
                <article className="admin-card admin-data-card">
                    <h3>Empresa / autonomo</h3>
                    <DataList items={[
                        ["Nombre fiscal", empresa.nombre_fiscal],
                        ["Nombre comercial", empresa.nombre_comercial],
                        ["NIF", empresa.nif],
                        ["Correo", empresa.correo],
                        ["Teléfono", empresa.telefono],
                        ["Dirección fiscal", empresa.direccion_fiscal],
                        ["Municipio", empresa.municipio],
                        ["Provincia", empresa.provincia],
                        ["Tipo empresa", empresa.tipo_empresa],
                        ["Estado", empresa.activa ? "Activo" : "No activo"],
                    ]} />
                </article>
                <article className="admin-card admin-data-card">
                    <h3>Responsable</h3>
                    <DataList items={[
                        ["Nombre", [responsable.nombre, responsable.apellido1, responsable.apellido2].filter(Boolean).join(" ")],
                        ["Email", responsable.email],
                        ["Teléfono", responsable.telefono],
                        ["Estado", responsable.activo ? "Activo" : "No activo"],
                    ]} />
                </article>
            </div>

            <section className="admin-card fases-card">
                <h3>Fases</h3>
                <div className="phase-grid">
                    {PHASES.map((phase) => {
                        const noAplica = phase.id === "representacion" && representacionNoAplica;
                        const estado = noAplica ? "No aplica" : solicitud[phase.stateKey];

                        return (
                            <div key={phase.id}>
                                <span>{phase.label}</span>
                                <div className="phase-actions">
                                    <EstadoSolicitudBadge estado={estado} />
                                    {!noAplica && !solicitudCerrada ? (
                                        <AdminActionsMenu
                                            actions={[
                                                {
                                                    label: "Aprobar fase",
                                                    onClick: () => onPhaseDecision?.("aprobar", phase.id, phase.label, solicitud.id),
                                                    variant: "success",
                                                    disabled: solicitud[phase.stateKey] === "aprobada",
                                                },
                                                {
                                                    label: "Rechazar fase",
                                                    onClick: () => onPhaseDecision?.("rechazar", phase.id, phase.label, solicitud.id),
                                                    variant: "danger",
                                                    disabled: solicitud[phase.stateKey] === "rechazada",
                                                },
                                            ]}
                                        />
                                    ) : null}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </section>

            <section className="admin-card documentos-section">
                <h3>Documentos</h3>
                <div className="document-groups">
                    {Object.entries(GROUP_LABELS).map(([group, label]) => (
                        <article key={group}>
                            <h4>{label}</h4>
                            {(documentos[group] || []).length ? (
                                documentos[group].map((documento) => (
                                    <div className="document-row" key={documento.id}>
                                        <div>
                                            <strong>{documento.nombre_original}</strong>
                                            <small>{documento.tipo_documento} · {documento.mime_type || "mime no indicado"} · {formatBytes(documento.tamano)}</small>
                                        </div>
                                        <AdminActionsMenu actions={[{ label: "Ver documento", onClick: () => onPreviewDocument(documento) }]} />
                                    </div>
                                ))
                            ) : (
                                <p className="admin-empty-inline">Sin documentos.</p>
                            )}
                        </article>
                    ))}
                </div>
            </section>

            <section className="admin-card historial-section">
                <h3>Historial</h3>
                {historial.length ? (
                    <div className="history-list">
                        {historial.map((evento) => (
                            <article key={evento.id}>
                                <strong>{evento.accion}</strong>
                                <span>{evento.admin?.nombre || evento.admin?.email || "Admin no indicado"}</span>
                                <small>
                                    {evento.estado_anterior || "Sin estado"} a {evento.estado_nuevo || "Sin estado"} · {formatDateTime(evento.created_at)}
                                </small>
                                {evento.motivo ? <p>{evento.motivo}</p> : null}
                            </article>
                        ))}
                    </div>
                ) : (
                    <p className="admin-empty-inline">Todavía no hay eventos de revisión.</p>
                )}
            </section>

            <section className="admin-card decision-zone">
                <h3>Decisión</h3>
                <div className="admin-actions">
                    <button type="button" className="admin-button admin-button-success" onClick={() => onDecision("aprobar", solicitud.id)} disabled={!puedeAprobarGlobal || solicitudCerrada}>
                        Aprobar solicitud
                    </button>
                    <button type="button" className="admin-button admin-button-danger" onClick={() => onDecision("rechazar", solicitud.id)} disabled={solicitudCerrada}>
                        Rechazar solicitud
                    </button>
                </div>
                {!puedeAprobarGlobal && !solicitudCerrada ? (
                    <p className="decision-hint">Aprueba primero las fases obligatorias para cerrar la solicitud.</p>
                ) : null}
            </section>
        </section>
    );
}

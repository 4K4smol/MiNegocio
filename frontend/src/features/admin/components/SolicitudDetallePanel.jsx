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

export function SolicitudDetallePanel({ solicitud, loading, onPreviewDocument, onDecision }) {
    if (loading) return <section className="admin-card"><p className="admin-loading">Cargando expediente...</p></section>;
    if (!solicitud) return <section className="admin-card admin-empty-inline">Selecciona una solicitud para revisar el expediente.</section>;

    const empresa = solicitud.empresa || {};
    const responsable = solicitud.responsable || {};
    const documentos = solicitud.documentos || {};
    const historial = solicitud.historial || [];

    return (
        <section className="solicitud-detail-panel">
            <div className="detail-heading admin-card">
                <div>
                    <span className="admin-kicker">Expediente #{solicitud.id}</span>
                    <h2>{empresa.nombre_fiscal || "Solicitud de registro"}</h2>
                    <p>{empresa.nif || "NIF no indicado"}</p>
                </div>
                <EstadoSolicitudBadge estado={solicitud.estado_actual || solicitud.estado_verificacion} />
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
                    <div><span>Identidad</span><EstadoSolicitudBadge estado={solicitud.estado_identidad} /></div>
                    <div><span>Empresa / actividad</span><EstadoSolicitudBadge estado={solicitud.estado_empresa} /></div>
                    <div><span>Representación</span><EstadoSolicitudBadge estado={solicitud.estado_representacion || "No aplica"} /></div>
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
                                        <button type="button" className="admin-button admin-button-ghost" onClick={() => onPreviewDocument(documento)}>
                                            Ver documento
                                        </button>
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
                    <button type="button" className="admin-button admin-button-success" onClick={() => onDecision("aprobar", solicitud.id)}>
                        Aprobar solicitud
                    </button>
                    <button type="button" className="admin-button admin-button-danger" onClick={() => onDecision("rechazar", solicitud.id)}>
                        Rechazar solicitud
                    </button>
                    <button type="button" className="admin-button admin-button-warning" onClick={() => onDecision("subsanacion", solicitud.id)}>
                        Pedir subsanacion
                    </button>
                </div>
            </section>
        </section>
    );
}

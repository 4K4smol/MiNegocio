import { useEffect, useState } from "react";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminFiltersBar } from "../components/AdminFiltersBar";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

const formatDateTime = (value) =>
    value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value)) : "Sin fecha";

export function AuditoriaAdminPage() {
    const [filters, setFilters] = useState({ texto: "", accion: "", fecha_desde: "", fecha_hasta: "" });
    const [eventos, setEventos] = useState([]);
    const [selected, setSelected] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    const load = () => {
        setLoading(true);
        setError("");
        adminApi.getAdminAuditoria({ ...filters, per_page: 80 })
            .then((response) => setEventos(response.items))
            .catch((apiError) => setError(apiError.message || "No se ha podido cargar la auditoria."))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters]);

    if (loading && !eventos.length) return <LoadingState>Cargando auditoria...</LoadingState>;

    return (
        <section className="admin-page">
            <AdminPageHeader title="Auditoria" description="Historial filtrable de acciones administrativas y revisiones." />
            <AdminFiltersBar>
                <input placeholder="Buscar accion, admin, empresa o motivo" value={filters.texto} onChange={(event) => setFilters({ ...filters, texto: event.target.value })} />
                <input placeholder="Accion" value={filters.accion} onChange={(event) => setFilters({ ...filters, accion: event.target.value })} />
                <input type="date" value={filters.fecha_desde} onChange={(event) => setFilters({ ...filters, fecha_desde: event.target.value })} />
                <input type="date" value={filters.fecha_hasta} onChange={(event) => setFilters({ ...filters, fecha_hasta: event.target.value })} />
            </AdminFiltersBar>
            {error ? <ErrorState>{error}</ErrorState> : null}
            <AdminDataTable
                columns={["Fecha", "Accion", "Admin", "Usuario afectado", "Empresa", "Estado", "Motivo", "Acciones"]}
                empty={!eventos.length ? <EmptyState title="Sin eventos" description="No hay acciones con los filtros actuales." /> : null}
            >
                {eventos.map((evento) => (
                    <tr key={evento.id}>
                        <td>{formatDateTime(evento.created_at)}</td>
                        <td><strong>{evento.accion}</strong></td>
                        <td>{evento.admin?.email || "No indicado"}</td>
                        <td>{evento.usuario?.email || "No aplica"}</td>
                        <td>{evento.empresa?.nombre_fiscal || "No aplica"}</td>
                        <td>{evento.estado_anterior || "Sin estado"} a {evento.estado_nuevo || "Sin estado"}</td>
                        <td>{evento.motivo || "Sin motivo"}</td>
                        <td>
                            <AdminActionsMenu actions={[
                                { label: "Ver detalle", onClick: () => setSelected(evento), variant: "primary" },
                            ]} />
                        </td>
                    </tr>
                ))}
            </AdminDataTable>
            {selected ? (
                <div className="admin-modal-backdrop" role="presentation">
                    <section className="admin-modal" role="dialog" aria-modal="true" aria-label="Detalle de auditoria">
                        <header>
                            <div>
                                <span className="admin-kicker">Auditoria</span>
                                <h3>{selected.accion}</h3>
                            </div>
                            <button type="button" className="admin-icon-button admin-button-ghost" onClick={() => setSelected(null)} aria-label="Cerrar">
                                X
                            </button>
                        </header>
                        <dl className="admin-data-list">
                            <div><dt>Fecha</dt><dd>{formatDateTime(selected.created_at)}</dd></div>
                            <div><dt>Admin</dt><dd>{selected.admin?.email || "No indicado"}</dd></div>
                            <div><dt>Usuario</dt><dd>{selected.usuario?.email || "No aplica"}</dd></div>
                            <div><dt>Empresa</dt><dd>{selected.empresa?.nombre_fiscal || "No aplica"}</dd></div>
                            <div><dt>Solicitud</dt><dd>{selected.solicitud_verificacion_id || "No aplica"}</dd></div>
                            <div><dt>Estado</dt><dd>{selected.estado_anterior || "Sin estado"} a {selected.estado_nuevo || "Sin estado"}</dd></div>
                        </dl>
                        {selected.motivo ? <p>{selected.motivo}</p> : null}
                        <pre className="admin-json">{JSON.stringify(selected.metadatos || {}, null, 2)}</pre>
                    </section>
                </div>
            ) : null}
        </section>
    );
}

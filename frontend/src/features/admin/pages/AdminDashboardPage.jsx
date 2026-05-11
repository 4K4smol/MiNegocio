import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { adminApi } from "../api/adminApi";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminStatsGrid } from "../components/AdminStatsGrid";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { EstadoSolicitudBadge } from "../components/EstadoSolicitudBadge";
import { LoadingState } from "../components/LoadingState";

const unwrap = (response) => response?.data ?? response;
const formatDate = (value) => value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium" }).format(new Date(value)) : "Sin fecha";

export function AdminDashboardPage() {
    const [dashboard, setDashboard] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        adminApi.getAdminDashboard()
            .then((response) => setDashboard(unwrap(response)))
            .catch((apiError) => setError(apiError.message || "No se ha podido cargar el dashboard."))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <LoadingState>Cargando dashboard...</LoadingState>;
    if (error) return <ErrorState>{error}</ErrorState>;

    const metricas = dashboard?.metricas || {};
    const solicitudes = dashboard?.solicitudes_recientes || [];
    const eventos = dashboard?.eventos_recientes || [];

    return (
        <section className="admin-page">
            <AdminPageHeader
                title="Dashboard administrador"
                description="Visión general de empresas, usuarios, solicitudes y actividad administrativa."
            />

            <AdminStatsGrid stats={[
                { title: "Total empresas", value: metricas.total_empresas },
                { title: "Empresas activas", value: metricas.empresas_activas },
                { title: "Pendientes", value: metricas.empresas_pendientes },
                { title: "Aprobadas", value: metricas.empresas_aprobadas },
                { title: "Rechazadas", value: metricas.empresas_rechazadas },
                { title: "Usuarios", value: metricas.usuarios_total },
                { title: "Usuarios activos", value: metricas.usuarios_activos },
                { title: "Docs pendientes", value: metricas.documentos_pendientes },
            ]} />

            <section className="admin-card quick-actions">
                <Link className="admin-button" to="/admin/solicitudes">Revisar solicitudes</Link>
                <Link className="admin-button admin-button-ghost" to="/admin/usuarios">Ver usuarios</Link>
                <Link className="admin-button admin-button-ghost" to="/admin/empresas">Ver empresas</Link>
                <Link className="admin-button admin-button-ghost" to="/admin/auditoria">Ver auditoría</Link>
                <Link className="admin-button admin-button-ghost" to="/admin/configuracion">Configuración global</Link>
            </section>

            <div className="admin-dashboard-grid">
                <section>
                    <div className="admin-card-header">
                        <h3>Solicitudes recientes</h3>
                    </div>
                    <AdminDataTable
                        columns={["Empresa", "Responsable", "Estado", "Fecha", "Acción"]}
                        empty={!solicitudes.length ? <EmptyState title="Sin solicitudes recientes" /> : null}
                    >
                        {solicitudes.map((solicitud) => (
                            <tr key={solicitud.id}>
                                <td>
                                    <strong>{solicitud.empresa?.nombre_fiscal}</strong>
                                    <small>{solicitud.empresa?.nif}</small>
                                </td>
                                <td>{solicitud.responsable?.email || "No indicado"}</td>
                                <td><EstadoSolicitudBadge estado={solicitud.estado_verificacion} /></td>
                                <td>{formatDate(solicitud.fecha_solicitud)}</td>
                                <td>
                                    <Link className="admin-link-action" to={`/admin/solicitudes/${solicitud.id}`}>
                                        Ver expediente
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </AdminDataTable>
                </section>
                <aside className="admin-card admin-activity">
                    <div className="admin-card-header">
                        <h3>Últimas acciones</h3>
                        <Link className="admin-link-action" to="/admin/auditoria">Ver todo</Link>
                    </div>
                    {eventos.length ? eventos.map((evento) => (
                        <article key={evento.id}>
                            <span>{formatDate(evento.created_at)}</span>
                            <strong>{evento.accion}</strong>
                            <p>{evento.motivo || evento.empresa?.nombre_fiscal || evento.usuario?.email || "Evento administrativo registrado."}</p>
                        </article>
                    )) : <p className="admin-empty-inline">Sin eventos recientes.</p>}
                </aside>
            </div>
        </section>
    );
}

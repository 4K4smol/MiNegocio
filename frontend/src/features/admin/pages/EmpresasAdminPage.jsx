import { useEffect, useState } from "react";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminConfirmModal } from "../components/AdminConfirmModal";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminFiltersBar } from "../components/AdminFiltersBar";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

export function EmpresasAdminPage() {
    const [filters, setFilters] = useState({ texto: "", estado: "", tipo_empresa: "" });
    const [empresas, setEmpresas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [confirm, setConfirm] = useState(null);

    const load = async () => {
        setLoading(true);
        setError("");
        try {
            const response = await adminApi.getAdminEmpresas({ ...filters, per_page: 50 });
            setEmpresas(response.items);
        } catch (apiError) {
            setError(apiError.message || "No se han podido cargar las empresas.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters]);

    const execute = async () => {
        setSaving(true);
        try {
            await confirm.action();
            setConfirm(null);
            await load();
        } catch (apiError) {
            setError(apiError.message || "No se ha podido actualizar la empresa.");
        } finally {
            setSaving(false);
        }
    };

    if (loading && !empresas.length) return <LoadingState>Cargando empresas...</LoadingState>;

    return (
        <section className="admin-page">
            <AdminPageHeader title="Empresas" description="Empresas y autonomos registrados, verificacion, usuarios y modulos." />
            <AdminFiltersBar>
                <input placeholder="Buscar empresa o NIF" value={filters.texto} onChange={(event) => setFilters({ ...filters, texto: event.target.value })} />
                <select value={filters.estado} onChange={(event) => setFilters({ ...filters, estado: event.target.value })}>
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="inactiva">Inactivas</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="aprobada">Aprobadas</option>
                    <option value="rechazada">Rechazadas</option>
                </select>
                <select value={filters.tipo_empresa} onChange={(event) => setFilters({ ...filters, tipo_empresa: event.target.value })}>
                    <option value="">Todos los tipos</option>
                    <option value="autonomo">Autonomo</option>
                    <option value="sociedad">Sociedad</option>
                    <option value="pyme">PYME</option>
                </select>
            </AdminFiltersBar>
            {error ? <ErrorState>{error}</ErrorState> : null}
            <AdminDataTable
                columns={["Empresa", "NIF", "Tipo", "Verificacion", "Estado", "Usuarios", "Modulos", "Acciones"]}
                empty={!empresas.length ? <EmptyState title="No hay empresas" description="Ajusta los filtros para ampliar la busqueda." /> : null}
            >
                {empresas.map((empresa) => {
                    const ultimaSolicitud = empresa.solicitudes_verificacion?.[0];
                    return (
                        <tr key={empresa.id}>
                            <td>
                                <strong>{empresa.nombre_comercial || empresa.nombre_fiscal}</strong>
                                <small>{empresa.nombre_fiscal}</small>
                            </td>
                            <td>{empresa.nif}</td>
                            <td>{empresa.tipo_empresa?.nombre || empresa.tipo_empresa || "No indicado"}</td>
                            <td><AdminStatusBadge estado={ultimaSolicitud?.estado_verificacion || "pendiente"} /></td>
                            <td><AdminStatusBadge estado={empresa.activa ? "aprobada" : "rechazada"} /></td>
                            <td>{empresa.usuarios?.length || 0}</td>
                            <td>{empresa.modulos?.filter((modulo) => modulo.activo).length || 0}</td>
                            <td>
                                <AdminActionsMenu actions={[
                                    { label: "Ver detalle", onClick: () => { window.location.href = `/admin/empresas/${empresa.id}`; }, variant: "primary" },
                                    {
                                        label: empresa.activa ? "Desactivar" : "Activar",
                                        variant: empresa.activa ? "warning" : "success",
                                        onClick: () => setConfirm({
                                            title: `${empresa.activa ? "Desactivar" : "Activar"} empresa`,
                                            description: empresa.nombre_fiscal,
                                            action: () => empresa.activa ? adminApi.desactivarEmpresa(empresa.id) : adminApi.activarEmpresa(empresa.id),
                                        }),
                                    },
                                ]} />
                            </td>
                        </tr>
                    );
                })}
            </AdminDataTable>
            <AdminConfirmModal
                open={Boolean(confirm)}
                title={confirm?.title}
                description={confirm?.description}
                loading={saving}
                onCancel={() => setConfirm(null)}
                onConfirm={execute}
            />
        </section>
    );
}

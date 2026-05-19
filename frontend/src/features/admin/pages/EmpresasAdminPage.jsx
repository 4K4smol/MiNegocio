import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { FilterField, SearchFilters, SearchInput } from "../../../shared/components/SearchFilters";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminConfirmModal } from "../components/AdminConfirmModal";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminPagination } from "../components/AdminPagination";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

const INITIAL_FILTERS = { texto: "", estado: "", tipo_empresa: "" };

export function EmpresasAdminPage() {
    const navigate = useNavigate();
    const [filters, setFilters] = useState(INITIAL_FILTERS);
    const [draftFilters, setDraftFilters] = useState(INITIAL_FILTERS);
    const [page, setPage] = useState(1);
    const [empresas, setEmpresas] = useState([]);
    const [meta, setMeta] = useState(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [confirm, setConfirm] = useState(null);

    const load = async () => {
        setLoading(true);
        setError("");
        try {
            const response = await adminApi.getAdminEmpresas({ ...filters, page, per_page: 15 });
            setEmpresas(response.items);
            setMeta(response.meta);
        } catch (apiError) {
            setError(apiError.message || "No se han podido cargar las empresas.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters, page]);

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

    const updateDraftFilter = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }));
    const applyFilters = () => {
        setFilters(draftFilters);
        setPage(1);
    };
    const resetFilters = () => {
        setDraftFilters(INITIAL_FILTERS);
        setFilters(INITIAL_FILTERS);
        setPage(1);
    };

    if (loading && !empresas.length) return <LoadingState>Cargando empresas...</LoadingState>;

    return (
        <section className="admin-page">
            <AdminPageHeader title="Empresas" description="Empresas y autonomos registrados, verificacion, usuarios y modulos." />
            <SearchFilters ariaLabel="Filtros de empresas" loading={loading} onReset={resetFilters} onSubmit={applyFilters}>
                <SearchInput
                    label="Buscar"
                    placeholder="Empresa o NIF"
                    value={draftFilters.texto}
                    onChange={(event) => updateDraftFilter("texto", event.target.value)}
                />
                <FilterField label="Estado">
                    <select value={draftFilters.estado} onChange={(event) => updateDraftFilter("estado", event.target.value)}>
                        <option value="">Todos los estados</option>
                        <option value="activa">Activas</option>
                        <option value="inactiva">Inactivas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="aprobada">Aprobadas</option>
                        <option value="rechazada">Rechazadas</option>
                    </select>
                </FilterField>
                <FilterField label="Tipo">
                    <select value={draftFilters.tipo_empresa} onChange={(event) => updateDraftFilter("tipo_empresa", event.target.value)}>
                        <option value="">Todos los tipos</option>
                        <option value="autonomo">Autonomo</option>
                        <option value="sociedad">Sociedad</option>
                        <option value="pyme">PYME</option>
                    </select>
                </FilterField>
            </SearchFilters>
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
                            <td><AdminStatusBadge estado={empresa.activa ? "activa" : "inactiva"} /></td>
                            <td>{empresa.usuarios?.length || 0}</td>
                            <td>{empresa.modulos?.filter((modulo) => modulo.activo).length || 0}</td>
                            <td>
                                <AdminActionsMenu actions={[
                                    { label: "Ver detalle", onClick: () => navigate(`/admin/empresas/${empresa.id}`), variant: "primary" },
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
            <AdminPagination meta={meta} disabled={loading} onPageChange={setPage} />
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

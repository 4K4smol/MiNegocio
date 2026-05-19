import { useCallback, useEffect, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { FilterField, SearchFilters, SearchInput } from "../../../shared/components/SearchFilters";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { servicioPayloadFromForm, validationErrors } from "../utils/serviciosForms";
import { serviciosService } from "../services/serviciosService";
import { ServicioFormModal } from "./ServicioFormModal";
import { ServicioPreciosModal } from "./ServicioPreciosModal";

export function ServiciosTab() {
    const [servicios, setServicios] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [filters, setFilters] = useState({ search: "", activo: "todos" });
    const [draftFilters, setDraftFilters] = useState(filters);
    const [saving, setSaving] = useState(false);
    const [formMode, setFormMode] = useState(null);
    const [selectedServicio, setSelectedServicio] = useState(null);
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [confirmAction, setConfirmAction] = useState(null);
    const [preciosServicio, setPreciosServicio] = useState(null);

    const loadData = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            const params = {
                per_page: 100,
                search: filters.search.trim() || undefined,
                activo: filters.activo === "todos" ? undefined : filters.activo === "activos",
            };
            const serviciosResponse = await serviciosService.list(params);
            setServicios(unwrapApiCollection(serviciosResponse));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar los servicios.");
        } finally {
            setLoading(false);
        }
    }, [filters]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const openCreate = () => {
        setSelectedServicio(null);
        setFormMode("create");
        resetForm();
    };

    const openEdit = (servicio) => {
        setSelectedServicio(servicio);
        setFormMode("edit");
        resetForm();
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedServicio(null);
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = servicioPayloadFromForm(event.currentTarget);
            if (formMode === "edit" && selectedServicio?.id) {
                await serviciosService.update(selectedServicio.id, payload);
            } else {
                await serviciosService.create(payload);
            }
            setFormMode(null);
            setSelectedServicio(null);
            resetForm();
            await loadData();
        } catch (currentError) {
            setFormErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido guardar el servicio.");
        } finally {
            setSaving(false);
        }
    };

    const handleToggleActivo = async () => {
        if (!confirmAction?.servicio) return;
        setSaving(true);
        try {
            const method = confirmAction.servicio.activo ? serviciosService.desactivar : serviciosService.activar;
            await method(confirmAction.servicio.id);
            setConfirmAction(null);
            await loadData();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido actualizar el estado.");
        } finally {
            setSaving(false);
        }
    };

    const updateDraftFilter = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }));
    const applyFilters = () => setFilters(draftFilters);
    const resetFilters = () => {
        const nextFilters = { search: "", activo: "todos" };
        setDraftFilters(nextFilters);
        setFilters(nextFilters);
    };

    return (
        <section className="module-section">
            <div className="section-toolbar">
                <div>
                    <h2>Mis servicios</h2>
                    <p>Crea servicios propios y define precios usando los tipos de tarifa globales de MiNegocio.</p>
                </div>
                <button className="button" type="button" onClick={openCreate}>
                    Nuevo servicio
                </button>
            </div>

            <SearchFilters ariaLabel="Filtros de servicios" loading={loading} onReset={resetFilters} onSubmit={applyFilters}>
                <SearchInput
                    label="Buscar"
                    placeholder="Nombre, codigo o descripcion"
                    value={draftFilters.search}
                    onChange={(event) => updateDraftFilter("search", event.target.value)}
                />
                <FilterField label="Estado">
                    <select value={draftFilters.activo} onChange={(event) => updateDraftFilter("activo", event.target.value)}>
                        <option value="todos">Todos</option>
                        <option value="activos">Activos</option>
                        <option value="inactivos">Inactivos</option>
                    </select>
                </FilterField>
            </SearchFilters>

            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando servicios...</LoadingState>
            ) : (
                <DataTable
                    columns={["Servicio", "Tipo/area", "Precios", "Unidad", "Duracion", "Estado", "Acciones"]}
                    empty={
                        !servicios.length ? (
                            <EmptyState
                                title="No hay servicios todavia"
                                description='Pulsa "Nuevo servicio" para crear tu primer servicio y despues configura sus precios.'
                            />
                        ) : null
                    }
                >
                    {servicios.map((servicio) => (
                        <tr key={servicio.id}>
                            <td>
                                <strong>{servicio.nombre}</strong>
                                {servicio.descripcion ? <small>{servicio.descripcion}</small> : null}
                            </td>
                            <td>{servicio.tipo_negocio || "No indicado"}</td>
                            <td>
                                <strong>{servicio.precios_count ?? 0}</strong>
                                <small>configurados</small>
                            </td>
                            <td>{servicio.unidad_servicio}</td>
                            <td>{servicio.duracion_estimada_min ? `${servicio.duracion_estimada_min} min` : "—"}</td>
                            <td>
                                <StatusBadge status={servicio.activo ? "activo" : "inactivo"} />
                            </td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        { label: "Editar", onClick: () => openEdit(servicio) },
                                        {
                                            label: "Gestionar precios",
                                            onClick: () => setPreciosServicio(servicio),
                                            variant: "primary",
                                        },
                                        {
                                            label: servicio.activo ? "Desactivar" : "Activar",
                                            variant: servicio.activo ? "danger" : "primary",
                                            onClick: () => setConfirmAction({ servicio }),
                                        },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            )}

            <ServicioFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                key={`${formMode || "closed"}-${selectedServicio?.id || "new"}`}
                loading={saving}
                mode={formMode || "create"}
                open={Boolean(formMode)}
                servicio={selectedServicio}
                onClose={closeForm}
                onSubmit={handleSubmit}
            />

            <ServicioPreciosModal
                open={Boolean(preciosServicio)}
                servicio={preciosServicio}
                onChanged={loadData}
                onClose={() => setPreciosServicio(null)}
            />

            <ConfirmModal
                confirmLabel={confirmAction?.servicio?.activo ? "Desactivar" : "Activar"}
                description="Se actualizara el estado del servicio para esta empresa."
                loading={saving}
                open={Boolean(confirmAction)}
                title={confirmAction?.servicio?.activo ? "Desactivar servicio" : "Activar servicio"}
                tone={confirmAction?.servicio?.activo ? "danger" : "success"}
                onCancel={() => setConfirmAction(null)}
                onConfirm={handleToggleActivo}
            />
        </section>
    );
}

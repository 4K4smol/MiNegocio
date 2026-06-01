import { useCallback, useEffect, useMemo, useState } from "react";
import { Briefcase, Clock, Hash, Pencil, Plus, Power, PowerOff, Ruler, Tags } from "lucide-react";
import { useLocation, useNavigate } from "react-router-dom";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { FilterField, SearchFilters, SearchInput } from "../../../shared/components/SearchFilters";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { formatCurrency } from "../../../shared/utils/formatters";
import { servicioPayloadFromForm, validationErrors } from "../utils/serviciosForms";
import { serviciosService } from "../services/serviciosService";
import { ServicioFormModal } from "./ServicioFormModal";
import { ServicioPreciosModal } from "./ServicioPreciosModal";
import "../styles/servicios.css";

const serviciosIndexPath = "/app/servicios";

const getPrecioList = (servicio) => {
    const precios = servicio?.precios?.data ?? servicio?.precios;
    return Array.isArray(precios) ? precios : [];
};

const getTarifaNombre = (precio) =>
    precio?.tarifa?.nombre ||
    precio?.tipo_tarifa_servicio?.nombre ||
    precio?.tipo_tarifa?.nombre ||
    "Tarifa";

const getTarifaCodigo = (precio) =>
    precio?.tarifa?.codigo ||
    precio?.tipo_tarifa_servicio?.codigo ||
    precio?.tipo_tarifa?.codigo ||
    getTarifaNombre(precio);

const getPrecioCount = (servicio) => servicio?.precios_count ?? getPrecioList(servicio).length ?? 0;

function ServicioMetaItem({ icon, label, value }) {
    const IconComponent = icon;

    return (
        <span className="servicio-meta-item">
            <IconComponent aria-hidden="true" size={16} />
            <span>{label}</span>
            <strong>{value}</strong>
        </span>
    );
}

function ServicioPrecioChips({ servicio }) {
    const precios = getPrecioList(servicio);
    const visiblePrecios = precios.slice(0, 4);
    const remaining = Math.max(precios.length - visiblePrecios.length, 0);

    if (!precios.length) {
        return <span className="servicio-price-empty">Sin precios configurados</span>;
    }

    return (
        <div className="servicio-price-chips" aria-label="Precios configurados">
            {visiblePrecios.map((precio) => (
                <span className="servicio-price-chip" key={`${getTarifaCodigo(precio)}-${precio.id}`}>
                    <span>{getTarifaNombre(precio)}</span>
                    <strong>{formatCurrency(precio.precio_base)}</strong>
                </span>
            ))}
            {remaining > 0 ? <span className="servicio-price-more">+{remaining}</span> : null}
        </div>
    );
}

export function ServiciosTab({ initialAction = null, initialServicioId = null }) {
    const navigate = useNavigate();
    const location = useLocation();
    const [servicios, setServicios] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingServicio, setLoadingServicio] = useState(false);
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

    const isRouteModal = location.pathname !== serviciosIndexPath;

    const goToIndex = useCallback(() => {
        if (isRouteModal) navigate(serviciosIndexPath, { replace: true });
    }, [isRouteModal, navigate]);

    const loadData = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            const params = {
                per_page: 100,
                include: "precios",
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

    const resetForm = useCallback(() => {
        setFormError("");
        setFormErrors({});
    }, []);

    const openCreate = useCallback(() => {
        setSelectedServicio(null);
        setFormMode("create");
        resetForm();
    }, [resetForm]);

    const openEditById = useCallback(async (servicioId) => {
        if (!servicioId) return;

        setLoadingServicio(true);
        setError("");
        resetForm();

        try {
            const response = await serviciosService.get(servicioId);
            setSelectedServicio(unwrapApiData(response));
            setFormMode("edit");
        } catch {
            setSelectedServicio(null);
            setFormMode(null);
            setError("El servicio ya no existe o no pertenece a tu empresa.");
        } finally {
            setLoadingServicio(false);
        }
    }, [resetForm]);

    useEffect(() => {
        if (initialAction === "create") {
            openCreate();
            return;
        }

        if (initialAction === "edit" && initialServicioId) {
            openEditById(initialServicioId);
        }
    }, [initialAction, initialServicioId, openCreate, openEditById]);

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedServicio(null);
        resetForm();
        goToIndex();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = servicioPayloadFromForm(event.currentTarget);

            if (formMode === "edit") {
                if (!selectedServicio?.id) {
                    throw new Error("No se ha podido identificar el servicio a editar.");
                }

                await serviciosService.update(selectedServicio.id, payload);
            } else {
                await serviciosService.create(payload);
            }

            setFormMode(null);
            setSelectedServicio(null);
            resetForm();
            await loadData();
            goToIndex();
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

    const serviciosActivos = useMemo(
        () => servicios.filter((servicio) => servicio.activo).length,
        [servicios],
    );

    return (
        <section className="module-section servicios-catalog">
            <div className="servicios-toolbar">
                <div>
                    <h2>Catálogo de servicios</h2>
                    <p>
                        {servicios.length} servicios · {serviciosActivos} activos
                    </p>
                </div>
                <button className="button servicios-primary-action" type="button" onClick={() => navigate("/app/servicios/nuevo")}>
                    <Plus aria-hidden="true" size={18} />
                    Nuevo servicio
                </button>
            </div>

            <SearchFilters ariaLabel="Filtros de servicios" loading={loading} onReset={resetFilters} onSubmit={applyFilters}>
                <SearchInput
                    label="Buscar"
                    placeholder="Nombre, código o descripción"
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
            {loadingServicio ? <LoadingState>Cargando servicio para editar...</LoadingState> : null}

            {loading ? (
                <LoadingState>Cargando servicios...</LoadingState>
            ) : servicios.length ? (
                <div className="servicios-grid" aria-label="Catálogo de servicios">
                    {servicios.map((servicio) => (
                        <article className="servicio-card" key={servicio.id}>
                            <div className="servicio-card__main">
                                <div className="servicio-card__header">
                                    <div className="servicio-card__identity">
                                        <span className="servicio-card__icon" aria-hidden="true">
                                            <Briefcase size={20} />
                                        </span>
                                        <div>
                                            <div className="servicio-card__title-row">
                                                <h3>{servicio.nombre}</h3>
                                                <StatusBadge status={servicio.activo ? "activo" : "inactivo"} />
                                            </div>
                                            <p>{servicio.descripcion || "Sin descripción añadida."}</p>
                                        </div>
                                    </div>
                                </div>

                                <div className="servicio-meta-grid">
                                    <ServicioMetaItem icon={Hash} label="Código" value={servicio.codigo || "Sin código"} />
                                    <ServicioMetaItem icon={Briefcase} label="Área" value={servicio.tipo_negocio || "No indicada"} />
                                    <ServicioMetaItem icon={Ruler} label="Unidad" value={servicio.unidad_servicio || "servicio"} />
                                    <ServicioMetaItem
                                        icon={Clock}
                                        label="Duración"
                                        value={servicio.duracion_estimada_min ? `${servicio.duracion_estimada_min} min` : "—"}
                                    />
                                </div>
                            </div>

                            <div className="servicio-card__pricing">
                                <div>
                                    <span className="servicio-card__kicker">Precios</span>
                                    <strong>{getPrecioCount(servicio)} configurados</strong>
                                </div>
                                <ServicioPrecioChips servicio={servicio} />
                            </div>

                            <div className="servicio-card__actions" aria-label={`Acciones de ${servicio.nombre}`}>
                                <button
                                    className="servicio-action-button"
                                    type="button"
                                    onClick={() => navigate(`/app/servicios/${servicio.id}/editar`)}
                                >
                                    <Pencil aria-hidden="true" size={17} />
                                    Editar
                                </button>
                                <button
                                    className="servicio-action-button servicio-action-button--primary"
                                    type="button"
                                    onClick={() => setPreciosServicio(servicio)}
                                >
                                    <Tags aria-hidden="true" size={17} />
                                    Gestionar precios
                                </button>
                                <button
                                    className={`servicio-action-button ${servicio.activo ? "servicio-action-button--danger" : "servicio-action-button--success"}`}
                                    type="button"
                                    onClick={() => setConfirmAction({ servicio })}
                                >
                                    {servicio.activo ? <PowerOff aria-hidden="true" size={17} /> : <Power aria-hidden="true" size={17} />}
                                    {servicio.activo ? "Desactivar" : "Activar"}
                                </button>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyState
                    title="No hay servicios todavía"
                    description='Pulsa "Nuevo servicio" para crear tu primer servicio y después configura sus precios.'
                />
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
                description="Se actualizará el estado del servicio para esta empresa."
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

import { useCallback, useEffect, useMemo, useState } from "react";
import { Pencil, Plus, Trash2 } from "lucide-react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { Modal } from "../../../shared/components/ui/Modal";
import { formatCurrency } from "../../../shared/utils/formatters";
import {
    servicioPrecioPayloadFromForm,
    validationErrors,
} from "../utils/serviciosForms";
import {
    servicioPreciosService,
    tiposTarifaServicioService,
} from "../services/serviciosService";
import { ServicioPrecioFormModal } from "./ServicioPrecioFormModal";

function getApiCollection(response) {
    const raw = response?.data ?? response;

    if (Array.isArray(raw)) return raw;
    if (Array.isArray(raw?.data)) return raw.data;
    if (Array.isArray(raw?.data?.data)) return raw.data.data;
    if (Array.isArray(raw?.items)) return raw.items;
    if (Array.isArray(raw?.data?.items)) return raw.data.items;
    if (Array.isArray(raw?.result)) return raw.result;
    if (Array.isArray(raw?.data?.result)) return raw.data.result;

    return [];
}

export function ServicioPreciosModal({ onClose, onChanged, open, servicio }) {
    const [precios, setPrecios] = useState([]);
    const [tiposTarifa, setTiposTarifa] = useState([]);

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    const [saving, setSaving] = useState(false);
    const [formMode, setFormMode] = useState(null);
    const [selectedPrecio, setSelectedPrecio] = useState(null);
    const [selectedTipoTarifaId, setSelectedTipoTarifaId] = useState(null);

    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});

    const [precioToDelete, setPrecioToDelete] = useState(null);

    const loadData = useCallback(async () => {
        if (!open || !servicio?.id) return;

        setLoading(true);
        setError("");

        try {
            const [preciosResponse, tiposTarifaResponse] = await Promise.all([
                servicioPreciosService.listByServicio(servicio.id),
                tiposTarifaServicioService.list(),
            ]);

            setPrecios(getApiCollection(preciosResponse));
            setTiposTarifa(getApiCollection(tiposTarifaResponse));
        } catch (currentError) {
            setError(
                currentError?.response?.data?.message ||
                    currentError?.message ||
                    "No se han podido cargar los precios del servicio.",
            );
        } finally {
            setLoading(false);
        }
    }, [open, servicio?.id]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const preciosPorTipoTarifa = useMemo(() => {
        const map = {};

        for (const precio of precios) {
            const tipoTarifaId =
                precio.tipo_tarifa_servicio_id ??
                precio.tipoTarifaServicioId ??
                precio.tipo_tarifa?.id ??
                precio.tipo_tarifa_servicio?.id;

            if (tipoTarifaId) {
                map[Number(tipoTarifaId)] = precio;
            }
        }

        return map;
    }, [precios]);

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const closeForm = () => {
        if (saving) return;

        setFormMode(null);
        setSelectedPrecio(null);
        setSelectedTipoTarifaId(null);
        resetForm();
    };

    const openAddPrecio = (tipoTarifaId) => {
        setSelectedPrecio(null);
        setSelectedTipoTarifaId(tipoTarifaId);
        setFormMode("create");
        resetForm();
    };

    const openEditPrecio = (precio) => {
        setSelectedPrecio(precio);
        setSelectedTipoTarifaId(null);
        setFormMode("edit");
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!servicio?.id) return;

        setSaving(true);
        resetForm();

        try {
            const payload = servicioPrecioPayloadFromForm(event.currentTarget);

            if (formMode === "edit" && selectedPrecio?.id) {
                await servicioPreciosService.update(selectedPrecio.id, payload);
            } else {
                await servicioPreciosService.createForServicio(
                    servicio.id,
                    payload,
                );
            }

            setFormMode(null);
            setSelectedPrecio(null);
            setSelectedTipoTarifaId(null);
            resetForm();

            await loadData();

            onChanged?.();
        } catch (currentError) {
            setFormErrors(validationErrors(currentError));
            setFormError(
                currentError?.response?.data?.message ||
                    currentError?.message ||
                    "No se ha podido guardar el precio.",
            );
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async () => {
        if (!precioToDelete) return;

        setSaving(true);

        try {
            await servicioPreciosService.remove(precioToDelete.id);

            setPrecioToDelete(null);

            await loadData();

            onChanged?.();
        } catch (currentError) {
            setError(
                currentError?.response?.data?.message ||
                    currentError?.message ||
                    "No se ha podido eliminar el precio.",
            );
        } finally {
            setSaving(false);
        }
    };

    const tipoTarifaParaFormulario = selectedTipoTarifaId
        ? tiposTarifa.find(
              (tipoTarifa) =>
                  Number(tipoTarifa.id) === Number(selectedTipoTarifaId),
          ) || null
        : null;

    return (
        <>
            <Modal
                footer={
                    <button
                        className="button button-ghost"
                        type="button"
                        onClick={onClose}
                    >
                        Cerrar
                    </button>
                }
                open={open}
                size="xl"
                subtitle={servicio?.codigo || "Servicio"}
                title={`Precios de ${servicio?.nombre || ""}`}
                onClose={onClose}
            >
                {error ? <ErrorState>{error}</ErrorState> : null}

                <p className="servicio-precios-intro">
                    Configura el importe por tipo de tarifa. Las órdenes usarán estas referencias al seleccionar el servicio.
                </p>

                {loading ? (
                    <LoadingState>Cargando precios...</LoadingState>
                ) : tiposTarifa.length === 0 ? (
                    <EmptyState
                        description="No hay tipos de tarifa activos disponibles. Revisa la configuración global de tipos de tarifa."
                        title="Sin tarifas configurables"
                    />
                ) : (
                    <div className="servicio-precios-matrix">
                        {tiposTarifa.map((tipoTarifa) => {
                            const precio =
                                preciosPorTipoTarifa[Number(tipoTarifa.id)];

                            return (
                                <article className="servicio-precio-row" key={tipoTarifa.id}>
                                    <div className="servicio-precio-row__tarifa">
                                        <strong>{tipoTarifa.nombre}</strong>
                                        <small>
                                            {tipoTarifa.descripcion ||
                                                "Tipo de tarifa global"}
                                        </small>
                                    </div>

                                    {precio ? (
                                        <>
                                            <div className="servicio-precio-metric">
                                                <span>Precio</span>
                                                <strong>{formatCurrency(precio.precio_base)}</strong>
                                            </div>

                                            <div className="servicio-precio-metric">
                                                <span>IVA</span>
                                                <strong>{precio.iva_porcentaje ?? 0}%</strong>
                                            </div>

                                            <div className="servicio-precio-metric">
                                                <span>Retención</span>
                                                <strong>{precio.retencion_porcentaje ?? 0}%</strong>
                                            </div>

                                            <div className="servicio-card__actions">
                                                <button
                                                    className="servicio-action-button"
                                                    type="button"
                                                    onClick={() => openEditPrecio(precio)}
                                                >
                                                    <Pencil aria-hidden="true" size={16} />
                                                    Editar
                                                </button>
                                                <button
                                                    className="servicio-action-button servicio-action-button--danger"
                                                    type="button"
                                                    onClick={() => setPrecioToDelete(precio)}
                                                >
                                                    <Trash2 aria-hidden="true" size={16} />
                                                    Quitar
                                                </button>
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <div className="servicio-precio-empty">Sin configurar</div>
                                            <div className="servicio-precio-metric">
                                                <span>IVA</span>
                                                <strong>—</strong>
                                            </div>
                                            <div className="servicio-precio-metric">
                                                <span>Retención</span>
                                                <strong>—</strong>
                                            </div>
                                            <div className="servicio-card__actions">
                                                <button
                                                    className="servicio-action-button servicio-action-button--primary"
                                                    type="button"
                                                    onClick={() => openAddPrecio(tipoTarifa.id)}
                                                >
                                                    <Plus aria-hidden="true" size={16} />
                                                    Añadir precio
                                                </button>
                                            </div>
                                        </>
                                    )}
                                </article>
                            );
                        })}
                    </div>
                )}
            </Modal>

            <ServicioPrecioFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                loading={saving}
                mode={formMode || "create"}
                open={Boolean(formMode)}
                precio={selectedPrecio}
                tipoTarifaPreseleccionado={tipoTarifaParaFormulario}
                tiposTarifa={tiposTarifa}
                onClose={closeForm}
                onSubmit={handleSubmit}
            />

            <ConfirmModal
                confirmLabel="Eliminar precio"
                description="Esta acción elimina el precio seleccionado del servicio."
                loading={saving}
                open={Boolean(precioToDelete)}
                title="Eliminar precio"
                onCancel={() => setPrecioToDelete(null)}
                onConfirm={handleDelete}
            />
        </>
    );
}

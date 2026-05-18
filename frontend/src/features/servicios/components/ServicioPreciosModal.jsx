import { useCallback, useEffect, useMemo, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { Modal } from "../../../shared/components/ui/Modal";
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

            const preciosData = getApiCollection(preciosResponse);
            const tiposTarifaData = getApiCollection(tiposTarifaResponse);

            setPrecios(preciosData);
            setTiposTarifa(tiposTarifaData);
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
                title={`Precios - ${servicio?.nombre || ""}`}
                onClose={onClose}
            >
                {error ? <ErrorState>{error}</ErrorState> : null}

                <p className="field-help">
                    Configura el precio de este servicio según el tipo de
                    tarifa: estándar, urgente, especial, fin de semana, festivo
                    o nocturno.
                </p>

                {loading ? (
                    <LoadingState>Cargando precios...</LoadingState>
                ) : tiposTarifa.length === 0 ? (
                    <EmptyState
                        description="No hay tipos de tarifa activos disponibles. Revisa la configuración global de tipos de tarifa."
                        title="Sin tarifas configurables"
                    />
                ) : (
                    <DataTable
                        columns={[
                            "Tipo de tarifa",
                            "Precio",
                            "IVA",
                            "Retención",
                            "Acciones",
                        ]}
                    >
                        {tiposTarifa.map((tipoTarifa) => {
                            const precio =
                                preciosPorTipoTarifa[Number(tipoTarifa.id)];

                            return (
                                <tr key={tipoTarifa.id}>
                                    <td>
                                        <strong>{tipoTarifa.nombre}</strong>
                                        <small>
                                            {tipoTarifa.descripcion ||
                                                "Tipo de tarifa global"}
                                        </small>
                                    </td>

                                    {precio ? (
                                        <>
                                            <td>
                                                {precio.precio_base}{" "}
                                                {precio.moneda || "EUR"}
                                            </td>

                                            <td>
                                                {precio.iva_porcentaje ?? 0}%
                                            </td>

                                            <td>
                                                {precio.retencion_porcentaje ??
                                                    0}
                                                %
                                            </td>

                                            <td>
                                                <RowActionsMenu
                                                    actions={[
                                                        {
                                                            label: "Editar",
                                                            onClick: () =>
                                                                openEditPrecio(
                                                                    precio,
                                                                ),
                                                        },
                                                        {
                                                            label: "Quitar",
                                                            variant: "danger",
                                                            onClick: () =>
                                                                setPrecioToDelete(
                                                                    precio,
                                                                ),
                                                        },
                                                    ]}
                                                />
                                            </td>
                                        </>
                                    ) : (
                                        <>
                                            <td colSpan={3}>
                                                <span className="text-muted">
                                                    Sin configurar
                                                </span>
                                            </td>

                                            <td>
                                                <RowActionsMenu
                                                    actions={[
                                                        {
                                                            label: "Añadir precio",
                                                            onClick: () =>
                                                                openAddPrecio(
                                                                    tipoTarifa.id,
                                                                ),
                                                        },
                                                    ]}
                                                />
                                            </td>
                                        </>
                                    )}
                                </tr>
                            );
                        })}
                    </DataTable>
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

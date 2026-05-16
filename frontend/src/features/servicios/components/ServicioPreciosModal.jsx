import { useCallback, useEffect, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { Modal } from "../../../shared/components/ui/Modal";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { servicioPrecioPayloadFromForm, validationErrors } from "../utils/serviciosForms";
import { servicioPreciosService } from "../services/serviciosService";
import { ServicioPrecioFormModal } from "./ServicioPrecioFormModal";

export function ServicioPreciosModal({ onClose, onChanged, open, servicio, tarifas = [] }) {
    const [precios, setPrecios] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [saving, setSaving] = useState(false);
    const [formMode, setFormMode] = useState(null);
    const [selectedPrecio, setSelectedPrecio] = useState(null);
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [precioToDelete, setPrecioToDelete] = useState(null);

    const loadPrecios = useCallback(async () => {
        if (!servicio?.id) return;
        setLoading(true);
        setError("");
        try {
            setPrecios(unwrapApiCollection(await servicioPreciosService.listByServicio(servicio.id)));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar los precios.");
        } finally {
            setLoading(false);
        }
    }, [servicio?.id]);

    useEffect(() => {
        if (open) loadPrecios();
    }, [open, loadPrecios]);

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedPrecio(null);
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();

        try {
            const payload = servicioPrecioPayloadFromForm(event.currentTarget);
            if (formMode === "edit" && selectedPrecio?.id) {
                await servicioPreciosService.update(selectedPrecio.id, payload);
            } else {
                await servicioPreciosService.createForServicio(servicio.id, payload);
            }
            setFormMode(null);
            setSelectedPrecio(null);
            resetForm();
            await loadPrecios();
            onChanged?.();
        } catch (currentError) {
            setFormErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido guardar el precio.");
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
            await loadPrecios();
            onChanged?.();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido eliminar el precio.");
        } finally {
            setSaving(false);
        }
    };

    return (
        <>
            <Modal
                footer={(
                    <>
                        <button className="button button-ghost" type="button" onClick={onClose}>
                            Cerrar
                        </button>
                        <button className="button" disabled={!tarifas.length} type="button" onClick={() => setFormMode("create")}>
                            Nuevo precio
                        </button>
                    </>
                )}
                open={open}
                size="xl"
                subtitle={servicio?.codigo || "Servicio"}
                title={`Precios de ${servicio?.nombre || ""}`}
                onClose={onClose}
            >
                {error ? <ErrorState>{error}</ErrorState> : null}
                {!tarifas.length ? (
                    <EmptyState
                        title="No hay tarifas disponibles"
                        description="Crea al menos una tarifa antes de configurar precios."
                    />
                ) : null}
                {loading ? (
                    <LoadingState>Cargando precios...</LoadingState>
                ) : (
                    <DataTable
                        columns={["Tarifa", "Precio", "IVA", "Retencion", "Vigencia", "Acciones"]}
                        empty={
                            !precios.length ? (
                                <EmptyState
                                    title="No hay precios configurados"
                                    description="Anade un precio para cada tarifa que uses en este servicio."
                                />
                            ) : null
                        }
                    >
                        {precios.map((precio) => (
                            <tr key={precio.id}>
                                <td>
                                    <strong>{precio.tarifa?.nombre || "Tarifa"}</strong>
                                    <small>{precio.tarifa?.codigo || "Sin codigo"}</small>
                                </td>
                                <td>{precio.precio_base} {precio.moneda}</td>
                                <td>{precio.iva_porcentaje ?? 0}%</td>
                                <td>{precio.retencion_porcentaje ?? 0}%</td>
                                <td>
                                    <span>{precio.vigente_desde || "Sin fecha"}</span>
                                    <small>Hasta: {precio.vigente_hasta || "Abierto"}</small>
                                </td>
                                <td>
                                    <RowActionsMenu
                                        actions={[
                                            {
                                                label: "Editar",
                                                onClick: () => {
                                                    setSelectedPrecio(precio);
                                                    setFormMode("edit");
                                                    resetForm();
                                                },
                                            },
                                            {
                                                label: "Eliminar",
                                                variant: "danger",
                                                onClick: () => setPrecioToDelete(precio),
                                            },
                                        ]}
                                    />
                                </td>
                            </tr>
                        ))}
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
                tarifas={tarifas}
                onClose={closeForm}
                onSubmit={handleSubmit}
            />

            <ConfirmModal
                confirmLabel="Eliminar precio"
                description="Esta accion elimina el precio seleccionado del servicio."
                loading={saving}
                open={Boolean(precioToDelete)}
                title="Eliminar precio"
                onCancel={() => setPrecioToDelete(null)}
                onConfirm={handleDelete}
            />
        </>
    );
}

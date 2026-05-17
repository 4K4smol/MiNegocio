import { useCallback, useEffect, useMemo, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
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
    const [selectedTarifaId, setSelectedTarifaId] = useState(null);
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

    const preciosPorTarifa = useMemo(() => {
        const map = {};
        for (const precio of precios) {
            map[precio.servicio_tarifa_id] = precio;
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
        setSelectedTarifaId(null);
        resetForm();
    };

    const openAddPrecio = (tarifaId) => {
        setSelectedPrecio(null);
        setSelectedTarifaId(tarifaId);
        setFormMode("create");
        resetForm();
    };

    const openEditPrecio = (precio) => {
        setSelectedPrecio(precio);
        setSelectedTarifaId(null);
        setFormMode("edit");
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
            setSelectedTarifaId(null);
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

    const tarifaParaFormulario = selectedTarifaId
        ? tarifas.find((t) => t.id === selectedTarifaId) || null
        : null;

    return (
        <>
            <Modal
                footer={(
                    <button className="button button-ghost" type="button" onClick={onClose}>
                        Cerrar
                    </button>
                )}
                open={open}
                size="xl"
                subtitle={servicio?.codigo || "Servicio"}
                title={`Precios — ${servicio?.nombre || ""}`}
                onClose={onClose}
            >
                {error ? <ErrorState>{error}</ErrorState> : null}

                {loading ? (
                    <LoadingState>Cargando precios...</LoadingState>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tarifa</th>
                                <th>Precio</th>
                                <th>IVA</th>
                                <th>Retencion</th>
                                <th>Vigencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {tarifas.map((tarifa) => {
                                const precio = preciosPorTarifa[tarifa.id];
                                return (
                                    <tr key={tarifa.id}>
                                        <td>
                                            <strong>{tarifa.nombre}</strong>
                                            {tarifa.es_default ? <small>Predeterminada</small> : null}
                                        </td>
                                        {precio ? (
                                            <>
                                                <td>{precio.precio_base} {precio.moneda}</td>
                                                <td>{precio.iva_porcentaje ?? 0}%</td>
                                                <td>{precio.retencion_porcentaje ?? 0}%</td>
                                                <td>
                                                    <span>{precio.vigente_desde || "Sin fecha"}</span>
                                                    <small>Hasta: {precio.vigente_hasta || "Abierto"}</small>
                                                </td>
                                                <td>
                                                    <div className="row-actions">
                                                        <button className="button button-sm" type="button" onClick={() => openEditPrecio(precio)}>
                                                            Editar
                                                        </button>
                                                        <button className="button button-sm button-danger" type="button" onClick={() => setPrecioToDelete(precio)}>
                                                            Quitar
                                                        </button>
                                                    </div>
                                                </td>
                                            </>
                                        ) : (
                                            <>
                                                <td colSpan={4}>
                                                    <span className="text-muted">Sin configurar</span>
                                                </td>
                                                <td>
                                                    <button className="button button-sm" type="button" onClick={() => openAddPrecio(tarifa.id)}>
                                                        Anadir precio
                                                    </button>
                                                </td>
                                            </>
                                        )}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
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
                tarifaPreseleccionada={tarifaParaFormulario}
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

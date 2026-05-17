import { useCallback, useEffect, useMemo, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { Modal } from "../../../shared/components/ui/Modal";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { servicioPrecioPayloadFromForm, validationErrors } from "../utils/serviciosForms";
import { servicioPreciosService } from "../services/serviciosService";
import { ServicioPrecioFormModal } from "./ServicioPrecioFormModal";

export function ServicioPreciosModal({ onClose, onChanged, open, servicio, tiposTarifa = [] }) {
    const [precios, setPrecios] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [saving, setSaving] = useState(false);
    const [formMode, setFormMode] = useState(null);
    const [selectedPrecio, setSelectedPrecio] = useState(null);
    const [selectedTipoTarifaId, setSelectedTipoTarifaId] = useState(null);
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

    const preciosPorTipoTarifa = useMemo(() => {
        const map = {};
        for (const precio of precios) {
            map[precio.tipo_tarifa_servicio_id] = precio;
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
            setSelectedTipoTarifaId(null);
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

    const tipoTarifaParaFormulario = selectedTipoTarifaId
        ? tiposTarifa.find((t) => t.id === selectedTipoTarifaId) || null
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
                title={`Precios - ${servicio?.nombre || ""}`}
                onClose={onClose}
            >
                {error ? <ErrorState>{error}</ErrorState> : null}
                <p className="field-help">
                    Los tipos de tarifa son definidos por MiNegocio. Aqui puedes indicar cuanto cuesta este servicio para cada tipo: Estandar, Urgente, Festivo o Nocturno.
                </p>

                {loading ? (
                    <LoadingState>Cargando precios...</LoadingState>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tipo de tarifa</th>
                                <th>Precio</th>
                                <th>IVA</th>
                                <th>Retencion</th>
                                <th>Vigencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {tiposTarifa.map((tipoTarifa) => {
                                const precio = preciosPorTipoTarifa[tipoTarifa.id];
                                return (
                                    <tr key={tipoTarifa.id}>
                                        <td>
                                            <strong>{tipoTarifa.nombre}</strong>
                                            <small>{tipoTarifa.descripcion || "Tipo global"}</small>
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
                                                    <RowActionsMenu
                                                        actions={[
                                                            { label: "Editar", onClick: () => openEditPrecio(precio) },
                                                            { label: "Quitar", variant: "danger", onClick: () => setPrecioToDelete(precio) },
                                                        ]}
                                                    />
                                                </td>
                                            </>
                                        ) : (
                                            <>
                                                <td colSpan={4}>
                                                    <span className="text-muted">Sin configurar</span>
                                                </td>
                                                <td>
                                                    <RowActionsMenu
                                                        actions={[{ label: "Anadir precio", onClick: () => openAddPrecio(tipoTarifa.id) }]}
                                                    />
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
                tipoTarifaPreseleccionado={tipoTarifaParaFormulario}
                tiposTarifa={tiposTarifa}
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

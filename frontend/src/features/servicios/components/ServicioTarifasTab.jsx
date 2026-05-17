import { useCallback, useEffect, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { servicioTarifaPayloadFromForm, validationErrors } from "../utils/serviciosForms";
import { servicioTarifasService } from "../services/serviciosService";
import { ServicioTarifaFormModal } from "./ServicioTarifaFormModal";

export function ServicioTarifasTab() {
    const [tarifas, setTarifas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [formMode, setFormMode] = useState(null);
    const [selectedTarifa, setSelectedTarifa] = useState(null);
    const [confirmAction, setConfirmAction] = useState(null);

    const loadTarifas = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setTarifas(unwrapApiCollection(await servicioTarifasService.list({ per_page: 100 })));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar las tarifas.");
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadTarifas();
    }, [loadTarifas]);

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const openCreate = () => {
        setSelectedTarifa(null);
        setFormMode("create");
        resetForm();
    };

    const openEdit = (tarifa) => {
        setSelectedTarifa(tarifa);
        setFormMode("edit");
        resetForm();
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedTarifa(null);
        resetForm();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = servicioTarifaPayloadFromForm(event.currentTarget);
            if (formMode === "edit" && selectedTarifa?.id) {
                await servicioTarifasService.update(selectedTarifa.id, payload);
            } else {
                await servicioTarifasService.create(payload);
            }
            setFormMode(null);
            setSelectedTarifa(null);
            await loadTarifas();
        } catch (currentError) {
            setFormErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido guardar la tarifa.");
        } finally {
            setSaving(false);
        }
    };

    const runAction = async () => {
        if (!confirmAction) return;
        setSaving(true);
        try {
            if (confirmAction.type === "default") {
                await servicioTarifasService.marcarDefault(confirmAction.tarifa.id);
            } else {
                const method = confirmAction.tarifa.activo ? servicioTarifasService.desactivar : servicioTarifasService.activar;
                await method(confirmAction.tarifa.id);
            }
            setConfirmAction(null);
            await loadTarifas();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido actualizar la tarifa.");
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="module-section">
            <div className="section-toolbar">
                <div>
                    <h2>Tarifas — configuracion avanzada</h2>
                    <p>
                        MiNegocio crea automaticamente las tarifas base (Estandar, Urgente, Especial, Fin de semana) cuando tu empresa queda activada.
                        Aqui puedes ver y gestionar esas tarifas. No es necesario crear tarifas nuevas para empezar a usar los servicios.
                    </p>
                </div>
                <button className="button button-ghost" type="button" onClick={openCreate}>
                    Nueva tarifa
                </button>
            </div>

            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando tarifas...</LoadingState>
            ) : (
                <DataTable
                    columns={["Nombre", "Codigo", "Descripcion", "Predeterminada", "Estado", "Acciones"]}
                    empty={
                        !tarifas.length ? (
                            <EmptyState
                                title="No hay tarifas"
                                description="Las tarifas base se crean automaticamente cuando la empresa es aprobada. Si no ves ninguna, contacta con soporte."
                            />
                        ) : null
                    }
                >
                    {tarifas.map((tarifa) => (
                        <tr key={tarifa.id}>
                            <td><strong>{tarifa.nombre}</strong></td>
                            <td>{tarifa.codigo}</td>
                            <td>{tarifa.descripcion || "Sin descripcion"}</td>
                            <td>{tarifa.es_default ? "Si" : "No"}</td>
                            <td><StatusBadge status={tarifa.activo ? "activo" : "inactivo"} /></td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        { label: "Editar", onClick: () => openEdit(tarifa) },
                                        {
                                            label: "Marcar predeterminada",
                                            hidden: tarifa.es_default,
                                            onClick: () => setConfirmAction({ type: "default", tarifa }),
                                        },
                                        {
                                            label: tarifa.activo ? "Desactivar" : "Activar",
                                            variant: tarifa.activo ? "danger" : "primary",
                                            onClick: () => setConfirmAction({ type: "estado", tarifa }),
                                        },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            )}

            <ServicioTarifaFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                key={`${formMode || "closed"}-${selectedTarifa?.id || "new"}`}
                loading={saving}
                mode={formMode || "create"}
                open={Boolean(formMode)}
                tarifa={selectedTarifa}
                onClose={closeForm}
                onSubmit={handleSubmit}
            />

            <ConfirmModal
                confirmLabel={confirmAction?.type === "default" ? "Marcar" : (confirmAction?.tarifa?.activo ? "Desactivar" : "Activar")}
                description="Se actualizara la configuracion de la tarifa para esta empresa."
                loading={saving}
                open={Boolean(confirmAction)}
                title={confirmAction?.type === "default" ? "Marcar tarifa predeterminada" : "Cambiar estado de tarifa"}
                tone={confirmAction?.tarifa?.activo && confirmAction?.type !== "default" ? "danger" : "success"}
                onCancel={() => setConfirmAction(null)}
                onConfirm={runAction}
            />
        </section>
    );
}

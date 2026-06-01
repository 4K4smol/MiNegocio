import { useState } from "react";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { ClienteSelector } from "./ClienteSelector";
import { OrdenLineasTable } from "./OrdenLineasTable";
import { OrdenPreviewPanel } from "./OrdenPreviewPanel";
import { PlanificacionOrdenPanel } from "./PlanificacionOrdenPanel";
import { ServicioSelectorModal } from "./ServicioSelectorModal";
import { useOrdenForm } from "../hooks/useOrdenForm";
import "../styles/ordenes.css";

export function OrdenForm({ disabled = false, errors = {}, initialOrden, onCancel, onSubmit, saving = false }) {
    const [selectorOpen, setSelectorOpen] = useState(false);
    const {
        addServiceLine,
        buildPayload,
        catalogError,
        catalogLoading,
        clientes,
        durationMinutes,
        form,
        lineas,
        removeLine,
        servicios,
        totals,
        updateForm,
        updateLine,
    } = useOrdenForm(initialOrden);

    const selectedCliente = clientes.find((cliente) => String(cliente.id) === String(form.cliente_id));
    const horaFinValida = !form.hora_inicio || !form.hora_fin || form.hora_fin > form.hora_inicio;
    const isBlocked = disabled || saving;

    const handleSubmit = (event) => {
        event.preventDefault();
        if (isBlocked || !lineas.length || !horaFinValida) return;
        onSubmit(buildPayload());
    };

    if (catalogLoading) return <LoadingState>Cargando clientes y servicios...</LoadingState>;
    if (catalogError) return <ErrorState>{catalogError}</ErrorState>;

    return (
        <form className="orden-edit-form" onSubmit={handleSubmit}>
            <div className="orden-edit-shell">
                <div className="wizard-step-body orden-edit-main">
                    <section className="orden-edit-section">
                        <ClienteSelector
                            clientes={clientes}
                            disabled={isBlocked}
                            errors={errors}
                            value={form.cliente_id}
                            onChange={(value) => updateForm("cliente_id", value)}
                        />
                    </section>

                    <section className="orden-edit-section">
                        <div className="orden-section-header">
                            <div>
                                <h2>Planificación</h2>
                                <p>Actualiza fecha, horario, prioridad y notas de trabajo.</p>
                            </div>
                        </div>
                        <PlanificacionOrdenPanel
                            disabled={isBlocked}
                            errors={errors}
                            form={form}
                            onChange={updateForm}
                        />
                    </section>

                    <section className="orden-edit-section">
                        <div className="page-header-row orden-section-header">
                            <div>
                                <h2>Servicios de la orden</h2>
                                <p>Selecciona servicios configurados y ajusta cantidad, precio, descuento e IVA.</p>
                            </div>
                            <button className="button button-ghost" disabled={isBlocked} type="button" onClick={() => setSelectorOpen(true)}>
                                Añadir servicio
                            </button>
                        </div>

                        <OrdenLineasTable
                            disabled={isBlocked}
                            errors={errors}
                            lineas={lineas}
                            totals={totals}
                            onRemove={removeLine}
                            onUpdate={updateLine}
                        />
                    </section>
                </div>

                <OrdenPreviewPanel
                    cliente={selectedCliente}
                    durationMinutes={durationMinutes}
                    form={form}
                    lineas={lineas}
                    pendingTitle="Para guardar la orden"
                    totals={totals}
                />
            </div>

            <footer className="wizard-actions orden-wizard-actions orden-edit-actions">
                {onCancel ? (
                    <button className="button button-ghost" disabled={saving} type="button" onClick={onCancel}>
                        Cancelar
                    </button>
                ) : null}
                <button className="button" disabled={isBlocked || !lineas.length || !horaFinValida} type="submit">
                    {saving ? "Guardando..." : "Guardar orden"}
                </button>
            </footer>

            <ServicioSelectorModal
                open={selectorOpen}
                servicios={servicios}
                onClose={() => setSelectorOpen(false)}
                onSelect={(servicio, precio) => {
                    addServiceLine(servicio, precio);
                    setSelectorOpen(false);
                }}
            />
        </form>
    );
}

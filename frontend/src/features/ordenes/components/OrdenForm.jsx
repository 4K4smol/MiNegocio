import { useState } from "react";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { ClienteSelector } from "./ClienteSelector";
import { LocalizacionSelector } from "./LocalizacionSelector";
import { OrdenLineasTable } from "./OrdenLineasTable";
import { PlanificacionOrdenPanel } from "./PlanificacionOrdenPanel";
import { ServicioSelectorModal } from "./ServicioSelectorModal";
import { useOrdenForm } from "../hooks/useOrdenForm";

export function OrdenForm({ disabled = false, errors = {}, initialOrden, onCancel, onSubmit, saving = false }) {
    const [selectorOpen, setSelectorOpen] = useState(false);
    const {
        addServiceLine,
        buildPayload,
        catalogError,
        catalogLoading,
        clientes,
        form,
        lineas,
        removeLine,
        servicios,
        totals,
        updateForm,
        updateLine,
    } = useOrdenForm(initialOrden);
    const selectedCliente = clientes.find((cliente) => String(cliente.id) === String(form.cliente_id));

    const handleSubmit = (event) => {
        event.preventDefault();
        onSubmit(buildPayload());
    };

    if (catalogLoading) return <LoadingState>Cargando clientes y servicios...</LoadingState>;
    if (catalogError) return <ErrorState>{catalogError}</ErrorState>;

    return (
        <form className="form" onSubmit={handleSubmit}>
            <div className="form-grid">
                <ClienteSelector
                    clientes={clientes}
                    disabled={disabled || saving}
                    errors={errors}
                    value={form.cliente_id}
                    onChange={(value) => updateForm("cliente_id", value)}
                    onLocalizacionChange={(value) => updateForm("localizacion_cliente_id", value)}
                />
                <LocalizacionSelector
                    cliente={selectedCliente}
                    disabled={disabled || saving}
                    errors={errors}
                    value={form.localizacion_cliente_id}
                    onChange={(value) => updateForm("localizacion_cliente_id", value)}
                />
            </div>

            <PlanificacionOrdenPanel
                disabled={disabled || saving}
                errors={errors}
                form={form}
                onChange={updateForm}
            />

            <div className="page-header-row">
                <div>
                    <h2>Servicios</h2>
                    <p>Selecciona servicios configurados y ajusta cantidad, precio, descuento e IVA.</p>
                </div>
                <button className="button button-ghost" disabled={disabled || saving} type="button" onClick={() => setSelectorOpen(true)}>
                    Anadir servicio
                </button>
            </div>

            <OrdenLineasTable
                disabled={disabled || saving}
                errors={errors}
                lineas={lineas}
                totals={totals}
                onRemove={removeLine}
                onUpdate={updateLine}
            />

            <footer className="modal-footer">
                {onCancel ? (
                    <button className="button button-ghost" disabled={saving} type="button" onClick={onCancel}>
                        Cancelar
                    </button>
                ) : null}
                <button className="button" disabled={saving || !lineas.length} type="submit">
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

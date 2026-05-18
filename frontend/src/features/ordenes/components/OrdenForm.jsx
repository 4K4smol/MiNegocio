import { useState } from "react";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { ClienteSelector } from "./ClienteSelector";
import { OrdenLineasTable } from "./OrdenLineasTable";
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
                    localizacionId={form.localizacion_id}
                    value={form.cliente_id}
                    onChange={(value) => updateForm("cliente_id", value)}
                    onLocalizacionChange={(value) => updateForm("localizacion_id", value)}
                />
                <label>
                    Fecha
                    <input
                        disabled={disabled || saving}
                        name="fecha"
                        required
                        type="date"
                        value={form.fecha}
                        onChange={(event) => updateForm("fecha", event.target.value)}
                    />
                </label>
                <label>
                    Hora
                    <input
                        disabled={disabled || saving}
                        name="hora"
                        required
                        type="time"
                        value={form.hora}
                        onChange={(event) => updateForm("hora", event.target.value)}
                    />
                </label>
                <label className="is-wide">
                    Notas para cliente
                    <textarea
                        disabled={disabled || saving}
                        name="notas_cliente"
                        rows={3}
                        value={form.notas_cliente}
                        onChange={(event) => updateForm("notas_cliente", event.target.value)}
                    />
                </label>
                <label className="is-wide">
                    Notas internas
                    <textarea
                        disabled={disabled || saving}
                        name="notas_internas"
                        rows={3}
                        value={form.notas_internas}
                        onChange={(event) => updateForm("notas_internas", event.target.value)}
                    />
                </label>
            </div>

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
                onSelect={(servicio) => {
                    addServiceLine(servicio);
                    setSelectorOpen(false);
                }}
            />
        </form>
    );
}


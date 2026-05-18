import { useEffect, useRef, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { Stepper } from "../../../shared/components/ui";
import { formatCurrency } from "../../../shared/utils/formatters";
import { useOrdenForm } from "../hooks/useOrdenForm";
import { ClienteSelector } from "./ClienteSelector";
import { OrdenLineasTable } from "./OrdenLineasTable";
import { ServicioSelectorModal } from "./ServicioSelectorModal";

const STEPS = [
    { label: "Cliente" },
    { label: "Servicios" },
    { label: "Detalles" },
    { label: "Resumen" },
];

function clienteLabel(cliente) {
    return (
        cliente?.razon_social ||
        [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") ||
        `Cliente ${cliente?.id}`
    );
}

export function OrdenCreateWizard({ error, errors = {}, onCancel, onSubmit, saving = false }) {
    const [step, setStep] = useState(1);
    const [selectorOpen, setSelectorOpen] = useState(false);
    const [searchParams] = useSearchParams();
    const preloadRef = useRef(false);

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
    } = useOrdenForm();

    /* Pre-cargar servicio desde query param ?servicio_id= */
    useEffect(() => {
        if (catalogLoading || preloadRef.current) return;
        const servicioId = searchParams.get("servicio_id");
        if (!servicioId) return;
        const servicio = servicios.find((s) => String(s.id) === servicioId);
        if (servicio) {
            addServiceLine(servicio);
            preloadRef.current = true;
        }
    }, [catalogLoading, servicios, searchParams, addServiceLine]);

    const selectedCliente = clientes.find((c) => String(c.id) === String(form.cliente_id));

    const canGoNext =
        (step === 1 && !!form.cliente_id) ||
        (step === 2 && lineas.length > 0) ||
        step === 3;

    const handleSubmit = () => onSubmit(buildPayload());

    if (catalogLoading) return <LoadingState />;
    if (catalogError) return <ErrorState>{catalogError}</ErrorState>;

    return (
        <div className="wizard">
            <Stepper currentStep={step} steps={STEPS} />

            <div className="wizard-step-body">
                {/* Paso 1 — Cliente */}
                {step === 1 && (
                    <div className="form-grid">
                        <ClienteSelector
                            clientes={clientes}
                            errors={errors}
                            localizacionId={form.localizacion_id}
                            value={form.cliente_id}
                            onChange={(v) => updateForm("cliente_id", v)}
                            onLocalizacionChange={(v) => updateForm("localizacion_id", v)}
                        />
                    </div>
                )}

                {/* Paso 2 — Servicios */}
                {step === 2 && (
                    <>
                        <div style={{ display: "flex", justifyContent: "flex-end" }}>
                            <button className="button" type="button" onClick={() => setSelectorOpen(true)}>
                                + Añadir servicio
                            </button>
                        </div>
                        <OrdenLineasTable
                            errors={errors}
                            lineas={lineas}
                            totals={totals}
                            onRemove={removeLine}
                            onUpdate={updateLine}
                        />
                        <ServicioSelectorModal
                            open={selectorOpen}
                            servicios={servicios}
                            onClose={() => setSelectorOpen(false)}
                            onSelect={(servicio) => {
                                addServiceLine(servicio);
                                setSelectorOpen(false);
                            }}
                        />
                    </>
                )}

                {/* Paso 3 — Detalles */}
                {step === 3 && (
                    <div className="form-grid">
                        <label>
                            Fecha
                            <input
                                required
                                type="date"
                                value={form.fecha}
                                onChange={(e) => updateForm("fecha", e.target.value)}
                            />
                            {errors.fecha_programada_inicio ? <small className="field-error">{errors.fecha_programada_inicio[0]}</small> : null}
                        </label>

                        <label>
                            Hora
                            <input
                                type="time"
                                value={form.hora}
                                onChange={(e) => updateForm("hora", e.target.value)}
                            />
                        </label>

                        <label className="is-wide">
                            Notas para el cliente
                            <textarea
                                placeholder="Instrucciones o información visible para el cliente..."
                                rows={3}
                                value={form.notas_cliente}
                                onChange={(e) => updateForm("notas_cliente", e.target.value)}
                            />
                        </label>

                        <label className="is-wide">
                            Notas internas
                            <textarea
                                placeholder="Notas solo visibles para el equipo..."
                                rows={3}
                                value={form.notas_internas}
                                onChange={(e) => updateForm("notas_internas", e.target.value)}
                            />
                        </label>
                    </div>
                )}

                {/* Paso 4 — Resumen */}
                {step === 4 && (
                    <>
                        {error ? <div className="form-error">{error}</div> : null}
                        <dl className="detail-list">
                            <div>
                                <dt>Cliente</dt>
                                <dd>{selectedCliente ? clienteLabel(selectedCliente) : "—"}</dd>
                            </div>
                            <div>
                                <dt>Fecha programada</dt>
                                <dd>{form.fecha && form.hora ? `${form.fecha} a las ${form.hora}` : form.fecha || "—"}</dd>
                            </div>
                            <div>
                                <dt>Servicios</dt>
                                <dd>{lineas.length} línea{lineas.length !== 1 ? "s" : ""}</dd>
                            </div>
                            <div>
                                <dt>Base imponible</dt>
                                <dd>{formatCurrency(totals.base_imponible)}</dd>
                            </div>
                            <div>
                                <dt>IVA estimado</dt>
                                <dd>{formatCurrency(totals.cuota_iva)}</dd>
                            </div>
                            <div>
                                <dt>Total estimado</dt>
                                <dd><strong>{formatCurrency(totals.total)}</strong></dd>
                            </div>
                        </dl>
                        {form.notas_cliente && (
                            <div>
                                <p style={{ fontWeight: 700, marginBottom: 4, fontSize: 13 }}>Notas para el cliente</p>
                                <p style={{ color: "var(--color-muted)", fontSize: 13, margin: 0 }}>{form.notas_cliente}</p>
                            </div>
                        )}
                    </>
                )}
            </div>

            <div className="wizard-actions" style={{ marginTop: 16 }}>
                <div style={{ display: "flex", gap: 8 }}>
                    {step > 1 && (
                        <button
                            className="button button-ghost"
                            disabled={saving}
                            type="button"
                            onClick={() => setStep((s) => s - 1)}
                        >
                            Anterior
                        </button>
                    )}
                    <button
                        className="button button-ghost"
                        disabled={saving}
                        type="button"
                        onClick={onCancel}
                    >
                        Cancelar
                    </button>
                </div>
                <div>
                    {step < 4 && (
                        <button
                            className="button"
                            disabled={!canGoNext}
                            type="button"
                            onClick={() => setStep((s) => s + 1)}
                        >
                            Siguiente →
                        </button>
                    )}
                    {step === 4 && (
                        <button
                            className="button"
                            disabled={saving}
                            type="button"
                            onClick={handleSubmit}
                        >
                            {saving ? "Creando orden..." : "Crear orden"}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

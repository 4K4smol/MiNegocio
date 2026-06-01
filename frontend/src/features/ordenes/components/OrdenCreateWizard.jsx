import { useEffect, useRef, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { Stepper } from "../../../shared/components/ui";
import { useOrdenForm } from "../hooks/useOrdenForm";
import { ClienteResumenCard } from "./ClienteResumenCard";
import { ClienteSelector } from "./ClienteSelector";
import { OrdenLineasTable } from "./OrdenLineasTable";
import { OrdenPreviewPanel } from "./OrdenPreviewPanel";
import { PlanificacionOrdenPanel } from "./PlanificacionOrdenPanel";
import { ResumenOrdenCard } from "./ResumenOrdenCard";
import { ServicioSelectorModal } from "./ServicioSelectorModal";
import "../styles/ordenes.css";

const STEPS = [
    { label: "Cliente" },
    { label: "Servicios" },
    { label: "Planificación" },
    { label: "Resumen" },
];

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
        durationMinutes,
        form,
        lineas,
        removeLine,
        servicios,
        totals,
        updateForm,
        updateLine,
    } = useOrdenForm();

    useEffect(() => {
        if (catalogLoading || preloadRef.current) return;
        const servicioId = searchParams.get("servicio_id");
        if (!servicioId) return;
        const servicio = servicios.find((current) => String(current.id) === servicioId);
        if (servicio) {
            addServiceLine(servicio, servicio.precios?.[0]);
            preloadRef.current = true;
        }
    }, [catalogLoading, servicios, searchParams, addServiceLine]);

    const selectedCliente = clientes.find((cliente) => String(cliente.id) === String(form.cliente_id));

    const horaFinValida = !form.hora_inicio || !form.hora_fin || form.hora_fin > form.hora_inicio;

    const canGoNext =
        (step === 1 && !!form.cliente_id) ||
        (step === 2 && lineas.length > 0) ||
        (step === 3 && horaFinValida);

    const handleSubmit = () => onSubmit(buildPayload());

    if (catalogLoading) return <LoadingState>Cargando clientes y servicios...</LoadingState>;
    if (catalogError) return <ErrorState>{catalogError}</ErrorState>;

    return (
        <div className="wizard orden-create-wizard">
            <Stepper currentStep={step} steps={STEPS} />

            <div className="orden-create-shell">
                <div className="wizard-step-body orden-create-main">
                    {step === 1 && (
                        <div className="orden-wizard-form">
                            <ClienteSelector
                                clientes={clientes}
                                errors={errors}
                                value={form.cliente_id}
                                onChange={(value) => updateForm("cliente_id", value)}
                            />
                            <ClienteResumenCard cliente={selectedCliente} />
                        </div>
                    )}

                    {step === 2 && (
                        <>
                            <div className="page-header-row orden-section-header">
                                <div>
                                    <h2>Servicios de la orden</h2>
                                    <p>Selecciona servicios configurados y ajusta cantidad, precio, descuento e IVA.</p>
                                </div>
                                <button className="button" type="button" onClick={() => setSelectorOpen(true)}>
                                    Añadir servicio
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
                                onSelect={(servicio, precio) => {
                                    addServiceLine(servicio, precio);
                                    setSelectorOpen(false);
                                }}
                            />
                        </>
                    )}

                    {step === 3 && (
                        <PlanificacionOrdenPanel
                            errors={errors}
                            form={form}
                            onChange={updateForm}
                        />
                    )}

                    {step === 4 && (
                        <ResumenOrdenCard
                            cliente={selectedCliente}
                            durationMinutes={durationMinutes}
                            error={error}
                            form={form}
                            lineas={lineas}
                            totals={totals}
                        />
                    )}
                </div>

                <OrdenPreviewPanel
                    cliente={selectedCliente}
                    durationMinutes={durationMinutes}
                    form={form}
                    lineas={lineas}
                    totals={totals}
                />
            </div>

            <div className="wizard-actions orden-wizard-actions">
                <div className="orden-wizard-actions__group">
                    {step > 1 && (
                        <button
                            className="button button-ghost"
                            disabled={saving}
                            type="button"
                            onClick={() => setStep((current) => current - 1)}
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
                            onClick={() => setStep((current) => current + 1)}
                        >
                            Siguiente
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

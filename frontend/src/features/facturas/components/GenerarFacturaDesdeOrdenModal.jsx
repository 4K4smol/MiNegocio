import { useState } from "react";
import { Modal } from "../../../shared/components/ui/Modal";
import { Stepper } from "../../../shared/components/ui/Stepper";
import { formatCurrency } from "../../../shared/utils/formatters";

const STEPS = [
    { label: "Orden" },
    { label: "Vista previa" },
    { label: "Confirmar" },
];

function clienteNombre(orden) {
    const c = orden?.cliente;
    return (
        c?.razon_social ||
        [c?.nombre, c?.apellidos].filter(Boolean).join(" ") ||
        "Sin cliente"
    );
}

export function GenerarFacturaDesdeOrdenModal({
    error,
    loading,
    onClose,
    onConfirm,
    open,
    orden,
}) {
    const [step, setStep] = useState(1);

    const lineasFacturables = (orden?.lineas || []).filter(
        (l) => l.facturable !== false,
    );
    const handleClose = () => {
        setStep(1);
        onClose?.();
    };

    const footer = (
        <>
            {step > 1 && (
                <button
                    className="button button-ghost"
                    disabled={loading}
                    type="button"
                    onClick={() => setStep((s) => s - 1)}
                >
                    ← Atrás
                </button>
            )}
            <button
                className="button button-ghost"
                disabled={loading}
                type="button"
                onClick={handleClose}
            >
                Cancelar
            </button>
            {step < 3 && (
                <button
                    className="button"
                    disabled={loading}
                    type="button"
                    onClick={() => setStep((s) => s + 1)}
                >
                    Siguiente →
                </button>
            )}
            {step === 3 && (
                <>
                    <button
                        className="button button-ghost"
                        disabled={loading}
                        type="button"
                        onClick={() => onConfirm("borrador")}
                    >
                        Guardar borrador
                    </button>
                    <button
                        className="button"
                        disabled={loading}
                        type="button"
                        onClick={() => onConfirm("emitir")}
                    >
                        {loading ? "Generando..." : "Emitir factura"}
                    </button>
                </>
            )}
        </>
    );

    return (
        <Modal
            closeDisabled={loading}
            footer={footer}
            open={open}
            size="lg"
            subtitle={orden?.numero}
            title="Generar factura"
            onClose={handleClose}
        >
            <Stepper currentStep={step} steps={STEPS} />

            {error ? (
                <div className="form-error" style={{ marginBottom: 12 }}>
                    {error}
                </div>
            ) : null}

            {/* Paso 1 — Datos de la orden */}
            {step === 1 && (
                <>
                    <p
                        style={{
                            color: "var(--color-muted)",
                            margin: "0 0 12px",
                        }}
                    >
                        Se generará una factura a partir de las líneas
                        facturables de esta orden completada.
                    </p>
                    <dl className="detail-list">
                        <div>
                            <dt>Orden</dt>
                            <dd>{orden?.numero || "—"}</dd>
                        </div>
                        <div>
                            <dt>Cliente</dt>
                            <dd>{clienteNombre(orden)}</dd>
                        </div>
                        <div>
                            <dt>Líneas facturables</dt>
                            <dd>{lineasFacturables.length}</dd>
                        </div>
                        <div>
                            <dt>Total estimado</dt>
                            <dd>
                                <strong>
                                    {formatCurrency(orden?.totales?.total)}
                                </strong>
                            </dd>
                        </div>
                    </dl>
                </>
            )}

            {/* Paso 2 — Vista previa de líneas */}
            {step === 2 && (
                <>
                    <p
                        style={{
                            color: "var(--color-muted)",
                            margin: "0 0 12px",
                            fontSize: 13,
                        }}
                    >
                        Revisa las líneas que se incluirán en la factura.
                    </p>
                    {lineasFacturables.length > 0 ? (
                        <table
                            className="data-table"
                            style={{ width: "100%", fontSize: 13 }}
                        >
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th style={{ textAlign: "right" }}>
                                        Cant.
                                    </th>
                                    <th style={{ textAlign: "right" }}>
                                        Precio
                                    </th>
                                    <th style={{ textAlign: "right" }}>IVA%</th>
                                    <th style={{ textAlign: "right" }}>
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {lineasFacturables.map((linea, i) => {
                                    const base =
                                        Number(linea.cantidad || 0) *
                                        Number(linea.precio_unitario || 0);
                                    const iva =
                                        (base *
                                            Number(linea.iva_porcentaje || 0)) /
                                        100;
                                    return (
                                        <tr key={i}>
                                            <td>
                                                {linea.descripcion ||
                                                    linea.servicio_nombre ||
                                                    "—"}
                                            </td>
                                            <td style={{ textAlign: "right" }}>
                                                {linea.cantidad}
                                            </td>
                                            <td style={{ textAlign: "right" }}>
                                                {formatCurrency(
                                                    linea.precio_unitario,
                                                )}
                                            </td>
                                            <td style={{ textAlign: "right" }}>
                                                {linea.iva_porcentaje}%
                                            </td>
                                            <td style={{ textAlign: "right" }}>
                                                {formatCurrency(base + iva)}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    ) : (
                        <p
                            style={{
                                color: "var(--color-muted)",
                                textAlign: "center",
                                padding: "20px 0",
                            }}
                        >
                            No hay líneas facturables en esta orden.
                        </p>
                    )}
                    <dl className="detail-list" style={{ marginTop: 12 }}>
                        <div>
                            <dt>Base imponible</dt>
                            <dd>
                                {formatCurrency(orden?.totales?.base_imponible)}
                            </dd>
                        </div>
                        <div>
                            <dt>IVA</dt>
                            <dd>{formatCurrency(orden?.totales?.cuota_iva)}</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd>
                                <strong>
                                    {formatCurrency(orden?.totales?.total)}
                                </strong>
                            </dd>
                        </div>
                    </dl>
                </>
            )}

            {/* Paso 3 — Aviso legal y confirmación */}
            {step === 3 && (
                <>
                    <div
                        className="notice-card notice-card--warning"
                        style={{ marginBottom: 16 }}
                    >
                        <strong>Antes de emitir</strong>
                        <p>
                            Una factura emitida no debe modificarse
                            directamente. Si necesitas corregirla, deberás
                            emitir una factura rectificativa. Las facturas
                            emitidas quedan registradas en el sistema fiscal y
                            no pueden eliminarse.
                        </p>
                    </div>
                    <dl className="detail-list">
                        <div>
                            <dt>Cliente</dt>
                            <dd>{clienteNombre(orden)}</dd>
                        </div>
                        <div>
                            <dt>Total a facturar</dt>
                            <dd>
                                <strong>
                                    {formatCurrency(orden?.totales?.total)}
                                </strong>
                            </dd>
                        </div>
                    </dl>
                    <p
                        style={{
                            color: "var(--color-muted)",
                            fontSize: 12,
                            marginTop: 12,
                        }}
                    >
                        Puedes guardar como <strong>borrador</strong> para
                        revisarla antes de emitirla, o{" "}
                        <strong>emitirla directamente</strong>.
                    </p>
                </>
            )}
        </Modal>
    );
}

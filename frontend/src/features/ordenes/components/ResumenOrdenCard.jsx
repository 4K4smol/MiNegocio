import { CalendarClock, ReceiptText, UserRound } from "lucide-react";
import { formatCurrency } from "../../../shared/utils/formatters";
import { calculateLine } from "../hooks/useOrdenForm";
import { clienteLabel, clienteLocation, formatOrdenDateTime } from "../utils/ordenDisplay";
import "../styles/ordenes.css";

export function ResumenOrdenCard({ cliente, durationMinutes = 0, error, form, lineas = [], totals }) {
    return (
        <section className="orden-final-summary">
            {error ? <div className="form-error">{error}</div> : null}

            <div className="orden-summary-grid">
                <article className="orden-summary-panel">
                    <div className="orden-summary-panel__title">
                        <UserRound aria-hidden="true" size={18} />
                        <span>Cliente</span>
                    </div>
                    <h3>{cliente ? clienteLabel(cliente) : "Sin cliente"}</h3>
                    <p>{cliente?.dni_cif || "Sin DNI/CIF"}</p>
                    <p>{[cliente?.email, cliente?.telefono].filter(Boolean).join(" · ") || "Sin contacto"}</p>
                    <p>{cliente ? clienteLocation(cliente) || "Sin ubicación" : "-"}</p>
                </article>

                <article className="orden-summary-panel">
                    <div className="orden-summary-panel__title">
                        <CalendarClock aria-hidden="true" size={18} />
                        <span>Planificación</span>
                    </div>
                    <h3>{formatOrdenDateTime(form)}</h3>
                    <p>Prioridad {form.prioridad_codigo || "normal"}</p>
                    <p>Duración estimada: {durationMinutes ? `${durationMinutes} min` : "-"}</p>
                </article>
            </div>

            <article className="orden-summary-panel">
                <div className="orden-summary-panel__title">
                    <ReceiptText aria-hidden="true" size={18} />
                    <span>Servicios</span>
                </div>
                <div className="orden-summary-lines">
                    {lineas.map((linea, index) => {
                        const calculated = calculateLine(linea);

                        return (
                            <div className="orden-summary-line" key={`${linea.servicio_id}-${index}`}>
                                <div>
                                    <strong>{linea.servicio_nombre}</strong>
                                    <span>{linea.tipo_tarifa_nombre || "Sin tarifa"} · {linea.cantidad} {linea.unidad_snapshot || "unidad"}</span>
                                </div>
                                <span>{formatCurrency(linea.precio_unitario)} · IVA {linea.iva_porcentaje}%</span>
                                <strong>{formatCurrency(calculated.total)}</strong>
                            </div>
                        );
                    })}
                </div>
            </article>

            <dl className="orden-summary-totals">
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
                    <dd>{formatCurrency(totals.total)}</dd>
                </div>
            </dl>
        </section>
    );
}

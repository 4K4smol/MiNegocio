import { AlertCircle, CalendarClock, CheckCircle2, Clock, FileText, ReceiptText, UserRound } from "lucide-react";
import { formatCurrency } from "../../../shared/utils/formatters";
import { clienteLabel, formatOrdenDateTime } from "../utils/ordenDisplay";
import "../styles/ordenes.css";

const getPendingItems = ({ cliente, form, lineas }) => {
    const items = [];

    if (!cliente) items.push("Selecciona un cliente.");
    if (!lineas.length) items.push("Añade al menos un servicio.");
    if (form.hora_inicio && form.hora_fin && form.hora_fin <= form.hora_inicio) {
        items.push("La hora de fin debe ser posterior a la hora de inicio.");
    }

    return items;
};

function PreviewMetric({ label, value }) {
    return (
        <div className="orden-preview-metric">
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

export function OrdenPreviewPanel({ cliente, durationMinutes = 0, form, lineas = [], pendingTitle = "Para crear la orden", totals }) {
    const pendingItems = getPendingItems({ cliente, form, lineas });

    return (
        <aside className="orden-live-preview" aria-label="Previsualización de la orden">
            <div className="orden-live-preview__header">
                <div>
                    <span className="eyebrow">Previsualización</span>
                    <h2>Orden en curso</h2>
                </div>
                {pendingItems.length ? (
                    <span className="orden-preview-status is-pending">
                        <AlertCircle aria-hidden="true" size={15} />
                        Pendiente
                    </span>
                ) : (
                    <span className="orden-preview-status is-ready">
                        <CheckCircle2 aria-hidden="true" size={15} />
                        Lista
                    </span>
                )}
            </div>

            <div className="orden-preview-block">
                <div className="orden-preview-block__title">
                    <UserRound aria-hidden="true" size={17} />
                    <span>Cliente</span>
                </div>
                <strong>{cliente ? clienteLabel(cliente) : "Sin seleccionar"}</strong>
                <small>{cliente?.telefono || cliente?.email || cliente?.dni_cif || "Añade un cliente para continuar."}</small>
            </div>

            <div className="orden-preview-block">
                <div className="orden-preview-block__title">
                    <CalendarClock aria-hidden="true" size={17} />
                    <span>Planificación</span>
                </div>
                <strong>{formatOrdenDateTime(form)}</strong>
                <small>Prioridad {form.prioridad_codigo || "normal"}</small>
            </div>

            <div className="orden-preview-lines">
                <div className="orden-preview-block__title">
                    <ReceiptText aria-hidden="true" size={17} />
                    <span>Servicios</span>
                </div>
                {lineas.length ? (
                    lineas.slice(0, 4).map((linea, index) => (
                        <div className="orden-preview-line" key={`${linea.servicio_id}-${index}`}>
                            <span>{linea.servicio_nombre}</span>
                            <strong>{formatCurrency(Number(linea.cantidad || 0) * Number(linea.precio_unitario || 0))}</strong>
                        </div>
                    ))
                ) : (
                    <small>No hay servicios añadidos.</small>
                )}
                {lineas.length > 4 ? <small>+{lineas.length - 4} servicios más</small> : null}
            </div>

            <div className="orden-preview-metrics">
                <PreviewMetric label="Base" value={formatCurrency(totals.base_imponible)} />
                <PreviewMetric label="IVA" value={formatCurrency(totals.cuota_iva)} />
                <PreviewMetric label="Total" value={formatCurrency(totals.total)} />
                <PreviewMetric label="Duración" value={durationMinutes ? `${durationMinutes} min` : "-"} />
            </div>

            {form.notas_cliente || form.notas_internas ? (
                <div className="orden-preview-block">
                    <div className="orden-preview-block__title">
                        <FileText aria-hidden="true" size={17} />
                        <span>Notas</span>
                    </div>
                    <small>{form.notas_cliente || form.notas_internas}</small>
                </div>
            ) : null}

            {pendingItems.length ? (
                <div className="orden-preview-pending">
                    <Clock aria-hidden="true" size={16} />
                    <div>
                        <strong>{pendingTitle}</strong>
                        {pendingItems.map((item) => <span key={item}>{item}</span>)}
                    </div>
                </div>
            ) : null}
        </aside>
    );
}

import { formatCurrency } from "../../../shared/utils/formatters";
import { clienteLabel } from "../utils/ordenDisplay";

export function ResumenOrdenCard({ cliente, durationMinutes = 0, error, form, lineas = [], totals }) {
    return (
        <>
            {error ? <div className="form-error">{error}</div> : null}
            <dl className="detail-list">
                <div>
                    <dt>Cliente</dt>
                    <dd>{cliente ? clienteLabel(cliente) : "-"}</dd>
                </div>
                <div>
                    <dt>Fecha prevista</dt>
                    <dd>{form.fecha ? `${form.fecha}${form.hora_inicio ? ` de ${form.hora_inicio}` : ""}${form.hora_fin ? ` a ${form.hora_fin}` : ""}` : "Sin fecha prevista"}</dd>
                </div>
                <div>
                    <dt>Prioridad</dt>
                    <dd>{form.prioridad_codigo || "normal"}</dd>
                </div>
                <div>
                    <dt>Servicios</dt>
                    <dd>{lineas.length} linea{lineas.length !== 1 ? "s" : ""}</dd>
                </div>
                <div>
                    <dt>Duracion estimada</dt>
                    <dd>{durationMinutes ? `${durationMinutes} min` : "-"}</dd>
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
        </>
    );
}

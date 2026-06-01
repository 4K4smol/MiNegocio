import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { formatCurrency } from "../../../shared/utils/formatters";
import { calculateLine } from "../hooks/useOrdenForm";

export function OrdenLineasTable({ disabled = false, errors = {}, lineas = [], onRemove, onUpdate, totals }) {
    if (!lineas.length) {
        return (
            <EmptyState
                title="Sin servicios"
                description="Añade uno o varios servicios para calcular el importe de la orden."
            />
        );
    }

    return (
        <>
            <DataTable
                tableClassName="orden-lineas-table"
                columns={["Servicio", "Tarifa", "Cantidad", "Precio", "Dto.", "IVA", "Total", ""]}
            >
                {lineas.map((linea, index) => {
                    const calculated = calculateLine(linea);
                    return (
                        <tr key={`${linea.servicio_id}-${index}`}>
                            <td>
                                <strong>{linea.servicio_nombre}</strong>
                                <input
                                    className="orden-line-input orden-line-input--description"
                                    disabled={disabled}
                                    value={linea.descripcion}
                                    onChange={(event) => onUpdate(index, "descripcion", event.target.value)}
                                />
                                <input
                                    className="orden-line-input orden-line-input--description"
                                    disabled={disabled}
                                    placeholder="Observaciones de la línea"
                                    value={linea.observaciones || ""}
                                    onChange={(event) => onUpdate(index, "observaciones", event.target.value)}
                                />
                            </td>
                            <td>
                                <span>{linea.tipo_tarifa_nombre || "Sin tarifa"}</span>
                            </td>
                            <td>
                                <input
                                    className="orden-line-input orden-line-input--number"
                                    disabled={disabled}
                                    min="0.01"
                                    step="0.01"
                                    type="number"
                                    value={linea.cantidad}
                                    onChange={(event) => onUpdate(index, "cantidad", event.target.value)}
                                />
                            </td>
                            <td>
                                <input
                                    className="orden-line-input orden-line-input--number"
                                    disabled={disabled}
                                    min="0"
                                    step="0.01"
                                    type="number"
                                    value={linea.precio_unitario}
                                    onChange={(event) => onUpdate(index, "precio_unitario", event.target.value)}
                                />
                            </td>
                            <td>
                                <input
                                    className="orden-line-input orden-line-input--number"
                                    disabled={disabled}
                                    max="100"
                                    min="0"
                                    step="0.01"
                                    type="number"
                                    value={linea.descuento_porcentaje}
                                    onChange={(event) => onUpdate(index, "descuento_porcentaje", event.target.value)}
                                />
                            </td>
                            <td>
                                <input
                                    className="orden-line-input orden-line-input--number"
                                    disabled={disabled}
                                    max="100"
                                    min="0"
                                    step="0.01"
                                    type="number"
                                    value={linea.iva_porcentaje}
                                    onChange={(event) => onUpdate(index, "iva_porcentaje", event.target.value)}
                                />
                            </td>
                            <td>{formatCurrency(calculated.total)}</td>
                            <td>
                                <button className="text-button" disabled={disabled} type="button" onClick={() => onRemove(index)}>
                                    Quitar
                                </button>
                            </td>
                        </tr>
                    );
                })}
            </DataTable>
            {errors.lineas ? <div className="form-error">{errors.lineas[0]}</div> : null}
            <dl className="detail-list">
                <div>
                    <dt>Base imponible</dt>
                    <dd>{formatCurrency(totals.base_imponible)}</dd>
                </div>
                <div>
                    <dt>IVA</dt>
                    <dd>{formatCurrency(totals.cuota_iva)}</dd>
                </div>
                <div>
                    <dt>Total estimado</dt>
                    <dd>{formatCurrency(totals.total)}</dd>
                </div>
            </dl>
        </>
    );
}

import { DataTable } from "../../../shared/components/DataTable";
import { formatCurrency, formatDate } from "../../../shared/utils/formatters";

export function FacturaPreview({ factura }) {
    if (!factura) return null;

    return (
        <div className="form-grid">
            <dl className="detail-list is-wide">
                <div>
                    <dt>Factura</dt>
                    <dd>{factura.serie ? `${factura.serie}-` : ""}{factura.numero || factura.id}</dd>
                </div>
                <div>
                    <dt>Fecha</dt>
                    <dd>{formatDate(factura.fecha_emision)}</dd>
                </div>
                <div>
                    <dt>Cliente</dt>
                    <dd>{factura.receptor_nombre_razon_social || factura.cliente?.nombre || "Sin cliente"}</dd>
                </div>
                <div>
                    <dt>Total</dt>
                    <dd>{formatCurrency(factura.total)}</dd>
                </div>
            </dl>
            {factura.lineas?.length ? (
                <DataTable columns={["Descripcion", "Cantidad", "Precio", "Total"]}>
                    {factura.lineas.map((linea) => (
                        <tr key={linea.id}>
                            <td>{linea.descripcion || linea.servicio_nombre_snapshot}</td>
                            <td>{linea.cantidad}</td>
                            <td>{formatCurrency(linea.precio_unitario)}</td>
                            <td>{formatCurrency(linea.total)}</td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}
        </div>
    );
}


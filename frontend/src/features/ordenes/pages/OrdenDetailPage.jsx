import { useCallback, useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { GenerarFacturaDesdeOrdenModal } from "../../facturas/components/GenerarFacturaDesdeOrdenModal";
import { DataTable } from "../../../shared/components/DataTable";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { formatCurrency, formatDate } from "../../../shared/utils/formatters";
import { OrdenEstadoBadge } from "../components/OrdenEstadoBadge";
import { ordenesApi } from "../services/ordenesApi";

const clienteNombre = (cliente) => cliente?.razon_social || [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") || "Sin cliente";

export function OrdenDetailPage() {
    const { ordenId } = useParams();
    const navigate = useNavigate();
    const [orden, setOrden] = useState(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [facturaOpen, setFacturaOpen] = useState(false);

    const loadOrden = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            const response = await ordenesApi.get(ordenId);
            setOrden(unwrapApiData(response));
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido cargar la orden.");
        } finally {
            setLoading(false);
        }
    }, [ordenId]);

    useEffect(() => {
        loadOrden();
    }, [loadOrden]);

    const runAction = async (action) => {
        setSaving(true);
        setError("");
        try {
            await action();
            await loadOrden();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido completar la accion.");
        } finally {
            setSaving(false);
        }
    };

    const handleGenerarFactura = async (mode = "emitir") => {
        if (!orden) return;
        await runAction(async () => {
            const response = await ordenesApi.generarFactura(orden.id, { modo: mode });
            const factura = unwrapApiData(response);
            setFacturaOpen(false);
            navigate(`/app/facturas/${factura.id}`);
        });
    };

    const estado = orden?.estado || "pendiente";
    const canComplete = orden && !["completada", "cancelada", "facturada"].includes(estado);

    return (
        <section className="page">
            <PageHeader
                actions={orden ? (
                    <>
                        <Link className="button button-ghost" to="/app/ordenes-trabajo">Volver</Link>
                        {canComplete ? (
                            <Link className="button button-ghost" to={`/app/ordenes-trabajo/${orden.id}/editar`}>Editar</Link>
                        ) : null}
                        {canComplete ? (
                            <button className="button" disabled={saving} type="button" onClick={() => runAction(() => ordenesApi.completar(orden.id))}>
                                Completar orden
                            </button>
                        ) : null}
                        {orden.puede_facturarse ? (
                            <button className="button" disabled={saving} type="button" onClick={() => setFacturaOpen(true)}>
                                Generar factura
                            </button>
                        ) : null}
                    </>
                ) : null}
                description="Consulta servicios, importes y estado de facturacion."
                title={orden?.numero || "Detalle de orden"}
            />

            {loading ? <LoadingState>Cargando orden...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading && orden ? (
                <>
                    <dl className="detail-list">
                        <div>
                            <dt>Cliente</dt>
                            <dd>{clienteNombre(orden.cliente)}</dd>
                        </div>
                        <div>
                            <dt>Estado</dt>
                            <dd><OrdenEstadoBadge estado={orden.estado} /></dd>
                        </div>
                        <div>
                            <dt>Fecha programada</dt>
                            <dd>{formatDate(orden.fechas?.programada_inicio)}</dd>
                        </div>
                        <div>
                            <dt>Localizacion</dt>
                            <dd>{orden.localizacion?.direccion_completa || orden.localizacion?.nombre || "Principal o sin especificar"}</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd>{formatCurrency(orden.totales?.total)}</dd>
                        </div>
                    </dl>

                    <DataTable columns={["Servicio", "Cantidad", "Precio", "Dto.", "IVA", "Total"]}>
                        {(orden.lineas || []).map((linea) => (
                            <tr key={linea.id}>
                                <td>
                                    <strong>{linea.servicio_nombre || linea.descripcion}</strong>
                                    {linea.descripcion ? <small>{linea.descripcion}</small> : null}
                                    {linea.meta?.tipo_tarifa_nombre ? <small>{linea.meta.tipo_tarifa_nombre}</small> : null}
                                </td>
                                <td>{linea.cantidad}</td>
                                <td>{formatCurrency(linea.precio_unitario)}</td>
                                <td>{linea.descuento_porcentaje}%</td>
                                <td>{linea.iva_porcentaje}%</td>
                                <td>{formatCurrency(linea.total)}</td>
                            </tr>
                        ))}
                    </DataTable>
                </>
            ) : null}

            <GenerarFacturaDesdeOrdenModal
                error={error}
                loading={saving}
                open={facturaOpen}
                orden={orden}
                onClose={() => !saving && setFacturaOpen(false)}
                onConfirm={handleGenerarFactura}
            />
        </section>
    );
}

import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { useResourceList } from "../../../shared/hooks/useResourceList";
import { formatCurrency, formatDate } from "../../../shared/utils/formatters";
import { facturasService } from "../services/facturasService";

export function FacturasPage() {
    const { error, items: facturas, loading, reload } = useResourceList(facturasService.list);

    const markAsPaid = async (facturaId) => {
        await facturasService.marcarPagada(facturaId);
        reload();
    };

    return (
        <section className="page">
            <PageHeader
                actions={
                    <>
                        <Link className="button button-ghost" to="/app/facturas/registros">
                            <AppIcon icon={appIcons.facturas} size={18} />
                            Registros
                        </Link>
                        <Link className="button button-ghost" to="/app/facturas/verifactu">
                            <AppIcon icon={appIcons.validar} size={18} />
                            VeriFactu
                        </Link>
                    </>
                }
                description="Consulta facturas, pagos y registros de facturación."
                title="Facturación"
            />

            {loading ? <LoadingState>Cargando facturas...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Factura", "Cliente", "Fecha", "Estado", "Total", "Acciones"]}
                    empty={
                        !facturas.length ? (
                            <EmptyState
                                title="No hay facturas emitidas"
                                description="Las facturas creadas desde órdenes completadas aparecerán en este listado."
                            />
                        ) : null
                    }
                >
                    {facturas.map((factura) => (
                        <tr key={factura.id}>
                            <td>
                                <strong>
                                    {factura.serie ? `${factura.serie}-` : ""}
                                    {factura.numero || factura.id}
                                </strong>
                                <small>{factura.tipo_factura || "Factura"}</small>
                            </td>
                            <td>
                                {factura.receptor_nombre_razon_social ||
                                    factura.cliente?.nombre ||
                                    "Sin cliente"}
                            </td>
                            <td>{formatDate(factura.fecha_emision)}</td>
                            <td>
                                <StatusBadge
                                    status={factura.pagada ? "pagada" : factura.estado_factura || "pendiente"}
                                />
                            </td>
                            <td>{formatCurrency(factura.total)}</td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        {
                                            label: "Ver detalle",
                                            to: `/app/facturas/${factura.id}`,
                                        },
                                        {
                                            label: "Marcar como pagada",
                                            disabled: factura.pagada,
                                            onClick: () => markAsPaid(factura.id),
                                        },
                                        { label: "Anular", disabled: true, variant: "danger" },
                                        { label: "Rectificar", disabled: true },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}
        </section>
    );
}

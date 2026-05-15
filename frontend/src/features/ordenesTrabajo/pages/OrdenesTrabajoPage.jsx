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
import { ordenesTrabajoService } from "../services/ordenesTrabajoService";

const getScheduledDate = (orden) =>
    orden.fechas?.programada_inicio || orden.fecha_programada_inicio || orden.fechas?.apertura;

export function OrdenesTrabajoPage() {
    const { error, items: ordenes, loading, reload } = useResourceList(
        ordenesTrabajoService.list,
    );

    const runAndReload = async (action) => {
        await action();
        reload();
    };

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to="/app/ordenes-trabajo/nueva">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nueva orden
                    </Link>
                }
                description="Planifica, completa y factura los trabajos de tu empresa."
                title="Órdenes"
            />

            {loading ? <LoadingState>Cargando órdenes...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Orden", "Cliente", "Fecha", "Estado", "Total", "Acciones"]}
                    empty={
                        !ordenes.length ? (
                            <EmptyState
                                title="No hay órdenes de trabajo"
                                description="Crea órdenes para planificar trabajos, asignar servicios y generar facturas."
                            />
                        ) : null
                    }
                >
                    {ordenes.map((orden) => {
                        const estado = orden.estado || orden.estado_codigo || "pendiente";
                        const canComplete = !["completada", "cancelada", "facturada"].includes(estado);
                        const canCancel = !["cancelada", "facturada"].includes(estado);

                        return (
                            <tr key={orden.id}>
                                <td>
                                    <strong>{orden.numero || `Orden ${orden.id}`}</strong>
                                    {orden.prioridad ? <small>Prioridad: {orden.prioridad}</small> : null}
                                </td>
                                <td>
                                    {orden.cliente?.nombre ||
                                        orden.cliente?.razon_social ||
                                        "Sin cliente"}
                                </td>
                                <td>{formatDate(getScheduledDate(orden))}</td>
                                <td>
                                    <StatusBadge status={estado} />
                                </td>
                                <td>{formatCurrency(orden.totales?.total)}</td>
                                <td>
                                    <RowActionsMenu
                                        actions={[
                                            {
                                                label: "Ver detalle",
                                                to: `/app/ordenes-trabajo/${orden.id}`,
                                            },
                                            {
                                                label: "Completar",
                                                disabled: !canComplete,
                                                onClick: () =>
                                                    runAndReload(() =>
                                                        ordenesTrabajoService.completar(orden.id),
                                                    ),
                                            },
                                            {
                                                label: "Cancelar",
                                                disabled: !canCancel,
                                                variant: "warning",
                                                confirmMessage: "¿Quieres cancelar esta orden?",
                                                confirm: true,
                                                onClick: () =>
                                                    runAndReload(() =>
                                                        ordenesTrabajoService.cancelar(orden.id),
                                                    ),
                                            },
                                            {
                                                label: "Generar factura",
                                                disabled: !orden.puede_facturarse,
                                                onClick: () =>
                                                    runAndReload(() =>
                                                        ordenesTrabajoService.generarFactura(orden.id),
                                                    ),
                                            },
                                        ]}
                                    />
                                </td>
                            </tr>
                        );
                    })}
                </DataTable>
            ) : null}
        </section>
    );
}

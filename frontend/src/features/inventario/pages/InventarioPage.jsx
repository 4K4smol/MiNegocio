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
import { formatCurrency } from "../../../shared/utils/formatters";
import { inventarioService } from "../services/inventarioService";

export function InventarioPage() {
    const { error, items, loading } = useResourceList(inventarioService.list);

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to="/app/inventario/nuevo">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nuevo ítem
                    </Link>
                }
                description="Controla materiales, productos, stock mínimo y movimientos."
                title="Inventario"
            />

            {loading ? <LoadingState>Cargando inventario...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Ítem", "SKU", "Stock", "Coste", "Estado", "Acciones"]}
                    empty={
                        !items.length ? (
                            <EmptyState
                                title="No hay ítems de inventario"
                                description="Añade materiales o productos para controlar existencias y costes."
                            />
                        ) : null
                    }
                >
                    {items.map((item) => (
                        <tr key={item.id}>
                            <td>
                                <strong>{item.nombre}</strong>
                                {item.descripcion ? <small>{item.descripcion}</small> : null}
                            </td>
                            <td>{item.sku || "Sin SKU"}</td>
                            <td>
                                <strong>{item.stock_actual ?? 0}</strong>
                                <small>Mínimo: {item.stock_minimo ?? 0}</small>
                            </td>
                            <td>{formatCurrency(item.coste_unitario)}</td>
                            <td>
                                <StatusBadge status={item.activo ? "activo" : "inactivo"} />
                            </td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        {
                                            label: "Editar",
                                            to: `/app/inventario/${item.id}/editar`,
                                        },
                                        { label: "Ver movimientos", disabled: true },
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

import { useMemo, useState } from "react";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";

export function InventarioUbicacionesTab({
    error,
    items = [],
    loading,
    onCreate,
    onEdit,
    onMovimiento,
    onToggleActivo,
    ubicaciones = [],
}) {
    const [ubicacionId, setUbicacionId] = useState("todas");
    const [stockBajo, setStockBajo] = useState(false);

    const filteredItems = useMemo(
        () =>
            items.filter((item) => {
                const ubicacionOk =
                    ubicacionId === "todas"
                        ? true
                        : String(
                              item.ubicacion?.id || item.ubicacion_id || "",
                          ) === String(ubicacionId);
                const stockOk = stockBajo ? item.stock_bajo : true;
                return ubicacionOk && stockOk;
            }),
        [items, stockBajo, ubicacionId],
    );

    return (
        <section className="card">
            <div className="page-header-row">
                <div>
                    <h2>Inventario</h2>
                    <p>
                        Consulta existencias por ubicacion y registra ajustes o
                        traslados cuando sea necesario.
                    </p>
                </div>
                <button className="button" type="button" onClick={onCreate}>
                    <AppIcon icon={appIcons.crear} size={18} />
                    Nuevo articulo
                </button>
            </div>

            <section
                className="filters-bar"
                aria-label="Filtros de inventario por ubicacion"
            >
                <select
                    value={ubicacionId}
                    onChange={(event) => setUbicacionId(event.target.value)}
                >
                    <option value="todas">Todas las ubicaciones</option>
                    {ubicaciones.map((ubicacion) => (
                        <option key={ubicacion.id} value={ubicacion.id}>
                            {ubicacion.nombre}
                        </option>
                    ))}
                </select>
                <label className="form-checkbox">
                    <input
                        checked={stockBajo}
                        type="checkbox"
                        onChange={(event) => setStockBajo(event.target.checked)}
                    />
                    Solo stock bajo
                </label>
            </section>

            {loading ? (
                <LoadingState>Cargando inventario...</LoadingState>
            ) : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={[
                        "Articulo",
                        "Ubicacion",
                        "Cantidad",
                        "Stock minimo",
                        "Estado",
                        "Acciones",
                    ]}
                    empty={
                        !filteredItems.length ? (
                            <EmptyState
                                title="No hay inventario asignado a esta ubicacion"
                                description="Asigna ubicacion a los articulos o registra movimientos de inventario."
                            />
                        ) : null
                    }
                >
                    {filteredItems.map((item) => (
                        <tr key={item.id}>
                            <td>
                                <strong>{item.nombre}</strong>
                                {item.sku ? <small>{item.sku}</small> : null}
                            </td>
                            <td>{item.ubicacion?.nombre || "Sin ubicacion"}</td>
                            <td>{item.stock_actual ?? 0}</td>
                            <td>{item.stock_minimo ?? 0}</td>
                            <td>
                                <StatusBadge
                                    label={
                                        item.stock_bajo
                                            ? "Stock bajo"
                                            : undefined
                                    }
                                    status={
                                        item.stock_bajo
                                            ? "pendiente"
                                            : item.activo
                                              ? "activo"
                                              : "inactivo"
                                    }
                                />
                            </td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        {
                                            label: "Editar",
                                            onClick: () => onEdit(item),
                                        },
                                        {
                                            label: "Registrar movimiento",
                                            variant: "primary",
                                            onClick: () => onMovimiento(item),
                                        },
                                        {
                                            label: item.activo
                                                ? "Desactivar"
                                                : "Activar",
                                            variant: item.activo
                                                ? "danger"
                                                : "primary",
                                            onClick: () =>
                                                onToggleActivo(item),
                                        },
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

import { useMemo, useState } from "react";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { FilterField, SearchFilters } from "../../../shared/components/SearchFilters";
import { StatusBadge } from "../../../shared/components/StatusBadge";

export function InventarioUbicacionesTab({
    error,
    items = [],
    loading,
    onCreate,
    onEdit,
    onMovimiento,
    ubicaciones = [],
}) {
    const [filters, setFilters] = useState({ ubicacionId: "todas", stockBajo: false });
    const [draftFilters, setDraftFilters] = useState(filters);

    const filteredItems = useMemo(
        () =>
            items.filter((item) => {
                const ubicacionOk =
                    filters.ubicacionId === "todas"
                        ? true
                        : String(
                              item.ubicacion?.id || item.ubicacion_id || "",
                          ) === String(filters.ubicacionId);
                const stockOk = filters.stockBajo ? item.stock_bajo : true;
                return ubicacionOk && stockOk;
            }),
        [items, filters],
    );

    const updateDraftFilter = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }));
    const resetFilters = () => {
        const nextFilters = { ubicacionId: "todas", stockBajo: false };
        setDraftFilters(nextFilters);
        setFilters(nextFilters);
    };

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

            <SearchFilters
                ariaLabel="Filtros de inventario por ubicacion"
                loading={loading}
                onReset={resetFilters}
                onSubmit={() => setFilters(draftFilters)}
            >
                <FilterField label="Ubicacion">
                    <select
                        value={draftFilters.ubicacionId}
                        onChange={(event) => updateDraftFilter("ubicacionId", event.target.value)}
                    >
                        <option value="todas">Todas las ubicaciones</option>
                        {ubicaciones.map((ubicacion) => (
                            <option key={ubicacion.id} value={ubicacion.id}>
                                {ubicacion.nombre}
                            </option>
                        ))}
                    </select>
                </FilterField>
                <FilterField label="Stock">
                    <span className="search-filters__checkbox">
                        <input
                            checked={draftFilters.stockBajo}
                            type="checkbox"
                            onChange={(event) => updateDraftFilter("stockBajo", event.target.checked)}
                        />
                        Solo stock bajo
                    </span>
                </FilterField>
            </SearchFilters>

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
                            </td>
                            <td>{item.ubicacion?.nombre || "Sin ubicacion"}</td>
                            <td>{item.stock_actual ?? 0}</td>
                            <td>{item.stock_minimo ?? 0}</td>
                            <td>
                                <StatusBadge
                                    label={item.stock_bajo ? "Stock bajo" : "Normal"}
                                    status={item.stock_bajo ? "pendiente" : "activo"}
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

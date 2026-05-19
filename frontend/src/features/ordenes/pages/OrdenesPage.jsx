import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { clientesService } from "../../clientes/services/clientesService";
import { GenerarFacturaDesdeOrdenModal } from "../../facturas/components/GenerarFacturaDesdeOrdenModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { FilterField, SearchFilters } from "../../../shared/components/SearchFilters";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { formatCurrency, formatDate } from "../../../shared/utils/formatters";
import { OrdenActionsDropdown } from "../components/OrdenActionsDropdown";
import { OrdenEstadoBadge } from "../components/OrdenEstadoBadge";
import { useOrdenes } from "../hooks/useOrdenes";
import { ordenesApi } from "../services/ordenesApi";

const scheduledDate = (orden) => orden.fechas?.programada_inicio || orden.fecha_programada_inicio || orden.fechas?.apertura;
const clienteNombre = (cliente) => cliente?.razon_social || [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") || "Sin cliente";

export function OrdenesPage() {
    const navigate = useNavigate();
    const { error, filters, loading, ordenes, reload, setFilters } = useOrdenes();
    const [clientes, setClientes] = useState([]);
    const [actionError, setActionError] = useState("");
    const [facturaOrden, setFacturaOrden] = useState(null);
    const [saving, setSaving] = useState(false);
    const [draftFilters, setDraftFilters] = useState(filters);

    useEffect(() => {
        clientesService.list({ per_page: 100 })
            .then((response) => setClientes(unwrapApiCollection(response)))
            .catch(() => setClientes([]));
    }, []);

    const runAction = async (action) => {
        setSaving(true);
        setActionError("");
        try {
            await action();
            await reload();
        } catch (currentError) {
            setActionError(currentError?.message || "No se ha podido completar la accion.");
        } finally {
            setSaving(false);
        }
    };

    const handleGenerarFactura = async (mode = "emitir") => {
        if (!facturaOrden) return;
        await runAction(async () => {
            const response = await ordenesApi.generarFactura(facturaOrden.id, { modo: mode });
            const factura = unwrapApiData(response);
            setFacturaOrden(null);
            navigate(`/app/facturas/${factura.id}`);
        });
    };

    const updateDraftFilter = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }));
    const applyFilters = () => setFilters(draftFilters);
    const resetFilters = () => {
        const nextFilters = { cliente_id: "", estado: "", fecha_desde: "", fecha_hasta: "" };
        setDraftFilters(nextFilters);
        setFilters(nextFilters);
    };

    return (
        <section className="page">
            <PageHeader
                actions={(
                    <Link className="button" to="/app/ordenes-trabajo/nueva">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nueva orden
                    </Link>
                )}
                description="Planifica trabajos, anade servicios y genera facturas al completarlos."
                title="Ordenes"
            />

            <SearchFilters ariaLabel="Filtros de ordenes" loading={loading} onReset={resetFilters} onSubmit={applyFilters}>
                <FilterField label="Estado">
                    <select value={draftFilters.estado} onChange={(event) => updateDraftFilter("estado", event.target.value)}>
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="completada">Completadas</option>
                        <option value="cancelada">Canceladas</option>
                        <option value="facturada">Facturadas</option>
                    </select>
                </FilterField>
                <FilterField label="Cliente">
                    <select value={draftFilters.cliente_id} onChange={(event) => updateDraftFilter("cliente_id", event.target.value)}>
                        <option value="">Todos los clientes</option>
                        {clientes.map((cliente) => <option key={cliente.id} value={cliente.id}>{clienteNombre(cliente)}</option>)}
                    </select>
                </FilterField>
                <FilterField label="Desde">
                    <input type="date" value={draftFilters.fecha_desde} onChange={(event) => updateDraftFilter("fecha_desde", event.target.value)} />
                </FilterField>
                <FilterField label="Hasta">
                    <input type="date" value={draftFilters.fecha_hasta} onChange={(event) => updateDraftFilter("fecha_hasta", event.target.value)} />
                </FilterField>
            </SearchFilters>

            {loading ? <LoadingState>Cargando ordenes...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {actionError ? <ErrorState>{actionError}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Orden", "Cliente", "Fecha", "Estado", "Total", "Acciones"]}
                    empty={!ordenes.length ? <EmptyState title="No hay ordenes de trabajo" description="Crea una orden para programar servicios y facturarlos despues." /> : null}
                >
                    {ordenes.map((orden) => (
                        <tr key={orden.id}>
                            <td>
                                <strong>{orden.numero || `Orden ${orden.id}`}</strong>
                                {orden.prioridad ? <small>Prioridad: {orden.prioridad}</small> : null}
                            </td>
                            <td>{clienteNombre(orden.cliente)}</td>
                            <td>{formatDate(scheduledDate(orden))}</td>
                            <td><OrdenEstadoBadge estado={orden.estado} /></td>
                            <td>{formatCurrency(orden.totales?.total)}</td>
                            <td>
                                <OrdenActionsDropdown
                                    orden={orden}
                                    onCancelar={(current) => runAction(() => ordenesApi.cancelar(current.id))}
                                    onCompletar={(current) => runAction(() => ordenesApi.completar(current.id))}
                                    onFacturar={(current) => setFacturaOrden(current)}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}

            <GenerarFacturaDesdeOrdenModal
                error={actionError}
                loading={saving}
                open={Boolean(facturaOrden)}
                orden={facturaOrden}
                onClose={() => !saving && setFacturaOrden(null)}
                onConfirm={handleGenerarFactura}
            />
        </section>
    );
}

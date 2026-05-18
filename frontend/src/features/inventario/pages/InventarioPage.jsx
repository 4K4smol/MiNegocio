import { useCallback, useEffect, useMemo, useState } from "react";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { FormModal } from "../../../shared/components/FormModal";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { Modal } from "../../../shared/components/ui/Modal";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { formatCurrency } from "../../../shared/utils/formatters";
import { InventarioItemFormModal } from "../components/InventarioItemFormModal";
import { InventarioMovimientoFormModal } from "../components/InventarioMovimientoFormModal";
import {
    inventarioItemsService,
    inventarioMovimientosService,
    inventarioUbicacionesService,
    inventarioUnidadesMedidaService,
    tiposInventarioMovimientoService,
} from "../services/inventarioService";

const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

const numberOrNull = (value) => {
    const normalized = emptyToNull(value);
    return normalized === null ? null : Number(normalized);
};

const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

const itemPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        nombre: emptyToNull(formData.get("nombre")),
        descripcion: emptyToNull(formData.get("descripcion")),
        sku: emptyToNull(formData.get("sku")),
        codigo_barras: emptyToNull(formData.get("codigo_barras")),
        unidad_medida_id: numberOrNull(formData.get("unidad_medida_id")),
        ubicacion_id: numberOrNull(formData.get("ubicacion_id")),
        stock_minimo: numberOrNull(formData.get("stock_minimo")) ?? 0,
        coste_unitario: numberOrNull(formData.get("coste_unitario")),
        activo: formData.get("activo") === "on",
    };
};

const movimientoPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        inventario_item_id: numberOrNull(formData.get("inventario_item_id")),
        tipo_movimiento_id: numberOrNull(formData.get("tipo_movimiento_id")),
        cantidad: numberOrNull(formData.get("cantidad")),
        stock_posterior: numberOrNull(formData.get("stock_posterior")),
        ubicacion_origen_id: numberOrNull(formData.get("ubicacion_origen_id")),
        ubicacion_destino_id: numberOrNull(formData.get("ubicacion_destino_id")),
        motivo: emptyToNull(formData.get("motivo")),
        fecha_movimiento: emptyToNull(formData.get("fecha_movimiento")),
    };
};

const catalogPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        nombre: emptyToNull(formData.get("nombre")),
        descripcion: emptyToNull(formData.get("descripcion")),
        tipo: emptyToNull(formData.get("tipo")),
        activo: true,
    };
};

function UbicacionFormModal({ error, loading, onClose, onSubmit, open }) {
    return (
        <FormModal
            error={error}
            loading={loading}
            open={open}
            submitLabel="Crear ubicacion"
            title="Nueva ubicacion"
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label>
                    Nombre
                    <input name="nombre" required />
                </label>
                <label>
                    Tipo
                    <input name="tipo" placeholder="Almacen, vehiculo..." />
                </label>
                <label className="is-wide">
                    Descripcion
                    <textarea name="descripcion" rows={3} />
                </label>
            </div>
        </FormModal>
    );
}

export function InventarioPage() {
    const [items, setItems] = useState([]);
    const [ubicaciones, setUbicaciones] = useState([]);
    const [unidades, setUnidades] = useState([]);
    const [tiposMovimiento, setTiposMovimiento] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [search, setSearch] = useState("");
    const [activo, setActivo] = useState("todos");
    const [ubicacionFilter, setUbicacionFilter] = useState("todos");
    const [stockBajo, setStockBajo] = useState(false);
    const [formMode, setFormMode] = useState(null);
    const [selectedItem, setSelectedItem] = useState(null);
    const [movementItem, setMovementItem] = useState(null);
    const [confirmItem, setConfirmItem] = useState(null);
    const [ubicacionFormOpen, setUbicacionFormOpen] = useState(false);
    const [movimientosItem, setMovimientosItem] = useState(null);
    const [movimientos, setMovimientos] = useState([]);
    const [loadingMovimientos, setLoadingMovimientos] = useState(false);

    const loadData = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            const params = {
                per_page: 100,
                search: search.trim() || undefined,
                activo: activo === "todos" ? undefined : activo === "activos",
                ubicacion_id: ubicacionFilter === "todos" ? undefined : ubicacionFilter,
                stock_bajo: stockBajo || undefined,
            };

            const [itemsResponse, ubicacionesResponse, unidadesResponse, tiposResponse] = await Promise.all([
                inventarioItemsService.list(params),
                inventarioUbicacionesService.list({ per_page: 100 }),
                inventarioUnidadesMedidaService.list({ per_page: 100 }),
                tiposInventarioMovimientoService.list({ per_page: 100 }),
            ]);

            setItems(unwrapApiCollection(itemsResponse));
            setUbicaciones(unwrapApiCollection(ubicacionesResponse));
            setUnidades(unwrapApiCollection(unidadesResponse));
            setTiposMovimiento(unwrapApiCollection(tiposResponse));
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido cargar el inventario.");
        } finally {
            setLoading(false);
        }
    }, [activo, search, stockBajo, ubicacionFilter]);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const ubicacionesActivas = useMemo(() => ubicaciones.filter((ubicacion) => ubicacion.activo), [ubicaciones]);
    const tiposMovimientoActivos = useMemo(() => tiposMovimiento.filter((tipo) => tipo.activo), [tiposMovimiento]);

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const openCreate = () => {
        setSelectedItem(null);
        setFormMode("create");
        resetForm();
    };

    const openEdit = (item) => {
        setSelectedItem(item);
        setFormMode("edit");
        resetForm();
    };

    const closeItemForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedItem(null);
        resetForm();
    };

    const handleItemSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = itemPayloadFromForm(event.currentTarget);
            if (formMode === "edit" && selectedItem?.id) {
                await inventarioItemsService.update(selectedItem.id, payload);
            } else {
                await inventarioItemsService.create(payload);
            }
            setFormMode(null);
            setSelectedItem(null);
            resetForm();
            await loadData();
        } catch (currentError) {
            setFormError(currentError?.message || "No se ha podido guardar el item.");
            setFormErrors(validationErrors(currentError));
        } finally {
            setSaving(false);
        }
    };

    const handleMovimientoSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            await inventarioMovimientosService.create(movimientoPayloadFromForm(event.currentTarget));
            setMovementItem(null);
            await loadData();
        } catch (currentError) {
            setFormError(currentError?.message || "No se ha podido registrar el movimiento.");
            setFormErrors(validationErrors(currentError));
        } finally {
            setSaving(false);
        }
    };

    const handleCatalogSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        setFormError("");
        try {
            const payload = catalogPayloadFromForm(event.currentTarget);
            await inventarioUbicacionesService.create(payload);
            setUbicacionFormOpen(false);
            await loadData();
        } catch (currentError) {
            setFormError(currentError?.message || "No se ha podido guardar.");
        } finally {
            setSaving(false);
        }
    };

    const handleToggleActivo = async () => {
        if (!confirmItem) return;
        setSaving(true);
        try {
            await inventarioItemsService.update(confirmItem.id, { activo: !confirmItem.activo });
            setConfirmItem(null);
            await loadData();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido actualizar el estado.");
        } finally {
            setSaving(false);
        }
    };

    const openMovimientos = async (item) => {
        setMovimientosItem(item);
        setLoadingMovimientos(true);
        try {
            setMovimientos(unwrapApiCollection(await inventarioMovimientosService.list({ inventario_item_id: item.id, per_page: 100 })));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar los movimientos.");
        } finally {
            setLoadingMovimientos(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                actions={(
                    <>
                        <button className="button button-ghost" type="button" onClick={() => setUbicacionFormOpen(true)}>
                            Nueva ubicacion
                        </button>
                        <button className="button" type="button" onClick={openCreate}>
                            <AppIcon icon={appIcons.crear} size={18} />
                            Nuevo item
                        </button>
                    </>
                )}
                description="Controla materiales, productos, stock minimo y movimientos."
                title="Inventario"
            />

            <section className="filters-bar" aria-label="Filtros de inventario">
                <input placeholder="Buscar por nombre, SKU o codigo de barras" value={search} onChange={(event) => setSearch(event.target.value)} />
                <select value={activo} onChange={(event) => setActivo(event.target.value)}>
                    <option value="todos">Todos</option>
                    <option value="activos">Activos</option>
                    <option value="inactivos">Inactivos</option>
                </select>
                <select value={ubicacionFilter} onChange={(event) => setUbicacionFilter(event.target.value)}>
                    <option value="todos">Todas las ubicaciones</option>
                    {ubicaciones.map((ubicacion) => (
                        <option key={ubicacion.id} value={ubicacion.id}>{ubicacion.nombre}</option>
                    ))}
                </select>
                <label className="form-checkbox">
                    <input checked={stockBajo} type="checkbox" onChange={(event) => setStockBajo(event.target.checked)} />
                    Stock bajo
                </label>
            </section>

            {loading ? <LoadingState>Cargando inventario...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Item", "SKU", "Ubicacion", "Stock actual", "Stock minimo", "Coste", "Valor stock", "Estado", "Acciones"]}
                    empty={
                        !items.length ? (
                            <EmptyState
                                title="No hay items de inventario"
                                description='Pulsa "Nuevo item" para registrar materiales o productos.'
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
                            <td>{item.ubicacion?.nombre || "No indicada"}</td>
                            <td>
                                <strong>{item.stock_actual ?? 0}</strong>
                                {item.stock_bajo ? <small>Stock bajo</small> : null}
                            </td>
                            <td>{item.stock_minimo ?? 0}</td>
                            <td>{formatCurrency(item.coste_unitario)}</td>
                            <td>{formatCurrency(item.valor_stock)}</td>
                            <td>
                                <StatusBadge status={item.activo ? "activo" : "inactivo"} />
                            </td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        { label: "Editar", onClick: () => openEdit(item) },
                                        { label: "Registrar movimiento", variant: "primary", onClick: () => { setMovementItem(item); resetForm(); } },
                                        { label: "Ver movimientos", onClick: () => openMovimientos(item) },
                                        {
                                            label: item.activo ? "Desactivar" : "Activar",
                                            variant: item.activo ? "danger" : "primary",
                                            onClick: () => setConfirmItem(item),
                                        },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}

            <InventarioItemFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                item={selectedItem}
                key={`${formMode || "closed"}-${selectedItem?.id || "new"}`}
                loading={saving}
                mode={formMode || "create"}
                open={Boolean(formMode)}
                ubicaciones={ubicacionesActivas}
                unidades={unidades}
                onClose={closeItemForm}
                onSubmit={handleItemSubmit}
            />

            <InventarioMovimientoFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                item={movementItem}
                key={`movimiento-${movementItem?.id || "closed"}`}
                loading={saving}
                open={Boolean(movementItem)}
                tiposMovimiento={tiposMovimientoActivos}
                ubicaciones={ubicacionesActivas}
                onClose={() => !saving && setMovementItem(null)}
                onSubmit={handleMovimientoSubmit}
            />

            <UbicacionFormModal
                error={formError}
                loading={saving}
                open={ubicacionFormOpen}
                onClose={() => !saving && setUbicacionFormOpen(false)}
                onSubmit={handleCatalogSubmit}
            />

            <ConfirmModal
                confirmLabel={confirmItem?.activo ? "Desactivar" : "Activar"}
                description="Se actualizara el estado del item para esta empresa."
                loading={saving}
                open={Boolean(confirmItem)}
                title={confirmItem?.activo ? "Desactivar item" : "Activar item"}
                tone={confirmItem?.activo ? "danger" : "success"}
                onCancel={() => setConfirmItem(null)}
                onConfirm={handleToggleActivo}
            />

            <Modal
                footer={<button className="button button-ghost" type="button" onClick={() => setMovimientosItem(null)}>Cerrar</button>}
                open={Boolean(movimientosItem)}
                size="xl"
                subtitle={movimientosItem?.sku || "Inventario"}
                title={`Movimientos - ${movimientosItem?.nombre || ""}`}
                onClose={() => setMovimientosItem(null)}
            >
                {loadingMovimientos ? (
                    <LoadingState>Cargando movimientos...</LoadingState>
                ) : (
                    <DataTable
                        columns={["Fecha", "Tipo", "Cantidad", "Stock anterior", "Stock posterior", "Motivo"]}
                        empty={!movimientos.length ? <EmptyState title="Sin movimientos" /> : null}
                    >
                        {movimientos.map((movimiento) => (
                            <tr key={movimiento.id}>
                                <td>{movimiento.fecha_movimiento ? new Date(movimiento.fecha_movimiento).toLocaleString("es-ES") : "Sin fecha"}</td>
                                <td>{movimiento.tipo_movimiento?.nombre || "Movimiento"}</td>
                                <td>{movimiento.cantidad}</td>
                                <td>{movimiento.stock_anterior}</td>
                                <td>{movimiento.stock_posterior}</td>
                                <td>{movimiento.motivo || "Sin motivo"}</td>
                            </tr>
                        ))}
                    </DataTable>
                )}
            </Modal>
        </section>
    );
}

import { useCallback, useEffect, useMemo, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { InventarioItemFormModal } from "../../inventario/components/InventarioItemFormModal";
import { InventarioMovimientoFormModal } from "../../inventario/components/InventarioMovimientoFormModal";
import {
    inventarioItemsService,
    inventarioMovimientosService,
    inventarioUbicacionesService,
    inventarioUnidadesMedidaService,
    tiposInventarioMovimientoService,
} from "../../inventario/services/inventarioService";
import { InventarioUbicacionesTab } from "../components/InventarioUbicacionesTab";
import { UbicacionFormModal } from "../components/UbicacionFormModal";
import { UbicacionesTab } from "../components/UbicacionesTab";

const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

const numberOrNull = (value) => {
    const normalized = emptyToNull(value);
    return normalized === null ? null : Number(normalized);
};

const validationErrors = (error) =>
    error?.errors || error?.payload?.errors || {};

const ubicacionPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        nombre: emptyToNull(formData.get("nombre")),
        direccion: emptyToNull(formData.get("direccion")),
        descripcion: emptyToNull(formData.get("descripcion")),
        observaciones: emptyToNull(formData.get("observaciones")),
        activo: formData.get("activo") === "on",
    };
};

const itemPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        nombre: emptyToNull(formData.get("nombre")),
        descripcion: emptyToNull(formData.get("descripcion")),
        unidad_medida_id: numberOrNull(formData.get("unidad_medida_id")),
        ubicacion_id: numberOrNull(formData.get("ubicacion_id")),
        stock_actual: numberOrNull(formData.get("stock_actual")) ?? 0,
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
        ubicacion_destino_id: numberOrNull(
            formData.get("ubicacion_destino_id"),
        ),
        motivo: emptyToNull(formData.get("motivo")),
        fecha_movimiento: emptyToNull(formData.get("fecha_movimiento")),
    };
};

export function EmpresaUbicacionesPage() {
    const [activeTab, setActiveTab] = useState("inventario");
    const [ubicaciones, setUbicaciones] = useState([]);
    const [items, setItems] = useState([]);
    const [unidades, setUnidades] = useState([]);
    const [tiposMovimiento, setTiposMovimiento] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [ubicacionFormMode, setUbicacionFormMode] = useState(null);
    const [selectedUbicacion, setSelectedUbicacion] = useState(null);
    const [confirmUbicacion, setConfirmUbicacion] = useState(null);
    const [itemFormMode, setItemFormMode] = useState(null);
    const [selectedItem, setSelectedItem] = useState(null);
    const [confirmItem, setConfirmItem] = useState(null);
    const [movementItem, setMovementItem] = useState(null);

    const loadData = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            const [
                ubicacionesResponse,
                itemsResponse,
                unidadesResponse,
                tiposResponse,
            ] =
                await Promise.all([
                    inventarioUbicacionesService.list({ per_page: 100 }),
                    inventarioItemsService.list({ per_page: 100 }),
                    inventarioUnidadesMedidaService.list({ per_page: 100 }),
                    tiposInventarioMovimientoService.list({ per_page: 100 }),
                ]);

            setUbicaciones(unwrapApiCollection(ubicacionesResponse));
            setItems(unwrapApiCollection(itemsResponse));
            setUnidades(unwrapApiCollection(unidadesResponse));
            setTiposMovimiento(unwrapApiCollection(tiposResponse));
        } catch (currentError) {
            setError(
                currentError?.message ||
                    "No se han podido cargar ubicaciones e inventario.",
            );
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadData();
    }, [loadData]);

    const ubicacionesActivas = useMemo(
        () => ubicaciones.filter((ubicacion) => ubicacion.activo),
        [ubicaciones],
    );
    const tiposMovimientoActivos = useMemo(
        () => tiposMovimiento.filter((tipo) => tipo.activo),
        [tiposMovimiento],
    );

    const resetForm = () => {
        setFormError("");
        setFormErrors({});
    };

    const openCreateUbicacion = () => {
        setSelectedUbicacion(null);
        setUbicacionFormMode("create");
        resetForm();
    };

    const openEditUbicacion = (ubicacion) => {
        setSelectedUbicacion(ubicacion);
        setUbicacionFormMode("edit");
        resetForm();
    };

    const closeUbicacionForm = () => {
        if (saving) return;
        setUbicacionFormMode(null);
        setSelectedUbicacion(null);
        resetForm();
    };

    const handleUbicacionSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = ubicacionPayloadFromForm(event.currentTarget);
            if (ubicacionFormMode === "edit" && selectedUbicacion?.id) {
                await inventarioUbicacionesService.update(
                    selectedUbicacion.id,
                    payload,
                );
            } else {
                await inventarioUbicacionesService.create(payload);
            }
            setUbicacionFormMode(null);
            setSelectedUbicacion(null);
            resetForm();
            await loadData();
        } catch (currentError) {
            setFormError(
                currentError?.message ||
                    "No se ha podido guardar la ubicacion.",
            );
            setFormErrors(validationErrors(currentError));
        } finally {
            setSaving(false);
        }
    };

    const openCreateItem = () => {
        setSelectedItem(null);
        setItemFormMode("create");
        resetForm();
    };

    const openEditItem = (item) => {
        setSelectedItem(item);
        setItemFormMode("edit");
        resetForm();
    };

    const closeItemForm = () => {
        if (saving) return;
        setItemFormMode(null);
        setSelectedItem(null);
        resetForm();
    };

    const handleItemSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            const payload = itemPayloadFromForm(event.currentTarget);
            if (itemFormMode === "edit" && selectedItem?.id) {
                await inventarioItemsService.update(selectedItem.id, payload);
            } else {
                await inventarioItemsService.create(payload);
            }
            setItemFormMode(null);
            setSelectedItem(null);
            resetForm();
            await loadData();
        } catch (currentError) {
            setFormError(
                currentError?.message ||
                    "No se ha podido guardar el articulo.",
            );
            setFormErrors(validationErrors(currentError));
        } finally {
            setSaving(false);
        }
    };

    const handleToggleUbicacion = async () => {
        if (!confirmUbicacion) return;
        setSaving(true);
        setError("");
        try {
            if (confirmUbicacion.activo) {
                await inventarioUbicacionesService.desactivar(
                    confirmUbicacion.id,
                );
            } else {
                await inventarioUbicacionesService.activar(confirmUbicacion.id);
            }
            setConfirmUbicacion(null);
            await loadData();
        } catch (currentError) {
            setError(
                currentError?.message ||
                    "No se ha podido actualizar la ubicacion.",
            );
        } finally {
            setSaving(false);
        }
    };

    const handleToggleItem = async () => {
        if (!confirmItem) return;
        setSaving(true);
        setError("");
        try {
            await inventarioItemsService.update(confirmItem.id, {
                activo: !confirmItem.activo,
            });
            setConfirmItem(null);
            await loadData();
        } catch (currentError) {
            setError(
                currentError?.message ||
                    "No se ha podido actualizar el articulo.",
            );
        } finally {
            setSaving(false);
        }
    };

    const handleMovimientoSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetForm();
        try {
            await inventarioMovimientosService.create(
                movimientoPayloadFromForm(event.currentTarget),
            );
            setMovementItem(null);
            await loadData();
        } catch (currentError) {
            setFormError(
                currentError?.message ||
                    "No se ha podido registrar el movimiento.",
            );
            setFormErrors(validationErrors(currentError));
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                description="Gestiona articulos, cantidades y ubicaciones de almacenaje desde una vista simple."
                title="Inventario"
            />

            <nav className="filters-bar" aria-label="Secciones de ubicaciones">
                <button
                    className={
                        activeTab === "inventario"
                            ? "button"
                            : "button button-ghost"
                    }
                    type="button"
                    onClick={() => setActiveTab("inventario")}
                >
                    Inventario
                </button>
                <button
                    className={
                        activeTab === "ubicaciones"
                            ? "button"
                            : "button button-ghost"
                    }
                    type="button"
                    onClick={() => setActiveTab("ubicaciones")}
                >
                    Ubicaciones
                </button>
            </nav>

            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando ubicaciones...</LoadingState>
            ) : null}

            {!loading && activeTab === "ubicaciones" ? (
                <UbicacionesTab
                    error=""
                    loading={false}
                    ubicaciones={ubicaciones}
                    onCreate={openCreateUbicacion}
                    onEdit={openEditUbicacion}
                    onToggleActivo={setConfirmUbicacion}
                />
            ) : null}

            {!loading && activeTab === "inventario" ? (
                <InventarioUbicacionesTab
                    error=""
                    items={items}
                    loading={false}
                    ubicaciones={ubicaciones}
                    onCreate={openCreateItem}
                    onEdit={openEditItem}
                    onMovimiento={(item) => {
                        setMovementItem(item);
                        resetForm();
                    }}
                    onToggleActivo={setConfirmItem}
                />
            ) : null}

            <UbicacionFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                key={`${ubicacionFormMode || "closed"}-${selectedUbicacion?.id || "new"}`}
                loading={saving}
                mode={ubicacionFormMode || "create"}
                open={Boolean(ubicacionFormMode)}
                ubicacion={selectedUbicacion}
                onClose={closeUbicacionForm}
                onSubmit={handleUbicacionSubmit}
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

            <InventarioItemFormModal
                disabled={saving}
                error={formError}
                errors={formErrors}
                item={selectedItem}
                key={`${itemFormMode || "closed"}-${selectedItem?.id || "new"}`}
                loading={saving}
                mode={itemFormMode || "create"}
                open={Boolean(itemFormMode)}
                ubicaciones={ubicacionesActivas}
                unidades={unidades}
                onClose={closeItemForm}
                onSubmit={handleItemSubmit}
            />

            <ConfirmModal
                confirmLabel={
                    confirmUbicacion?.activo ? "Desactivar" : "Activar"
                }
                description="Se actualizara el estado de esta ubicacion."
                loading={saving}
                open={Boolean(confirmUbicacion)}
                title={
                    confirmUbicacion?.activo
                        ? "Desactivar ubicacion"
                        : "Activar ubicacion"
                }
                tone={confirmUbicacion?.activo ? "danger" : "success"}
                onCancel={() => setConfirmUbicacion(null)}
                onConfirm={handleToggleUbicacion}
            />

            <ConfirmModal
                confirmLabel={confirmItem?.activo ? "Desactivar" : "Activar"}
                description="Se actualizara el estado de este articulo."
                loading={saving}
                open={Boolean(confirmItem)}
                title={
                    confirmItem?.activo
                        ? "Desactivar articulo"
                        : "Activar articulo"
                }
                tone={confirmItem?.activo ? "danger" : "success"}
                onCancel={() => setConfirmItem(null)}
                onConfirm={handleToggleItem}
            />
        </section>
    );
}

import { FormModal } from "../../../shared/components/FormModal";

const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export function InventarioItemFormModal({
    disabled = false,
    error,
    errors = {},
    item,
    loading = false,
    mode = "create",
    onClose,
    onSubmit,
    open,
    ubicaciones = [],
    unidades = [],
}) {
    const isEdit = mode === "edit";

    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={isEdit ? "Guardar item" : "Crear item"}
            title={isEdit ? "Editar item" : "Nuevo item"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label className="is-wide">
                    Nombre
                    <input defaultValue={item?.nombre || ""} disabled={disabled} name="nombre" required />
                    {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                </label>

                <label>
                    Unidad de medida
                    <select defaultValue={item?.unidad_medida_id || ""} disabled={disabled} name="unidad_medida_id" required>
                        <option value="">Selecciona unidad</option>
                        {unidades.map((unidad) => (
                            <option key={unidad.id} value={unidad.id}>{unidad.nombre}</option>
                        ))}
                    </select>
                    {fieldError(errors, "unidad_medida_id") ? <small className="field-error">{fieldError(errors, "unidad_medida_id")}</small> : null}
                </label>

                <label>
                    Ubicacion
                    <select defaultValue={item?.ubicacion_id || ""} disabled={disabled} name="ubicacion_id">
                        <option value="">Sin ubicacion</option>
                        {ubicaciones.map((ubicacion) => (
                            <option key={ubicacion.id} value={ubicacion.id}>{ubicacion.nombre}</option>
                        ))}
                    </select>
                    {fieldError(errors, "ubicacion_id") ? <small className="field-error">{fieldError(errors, "ubicacion_id")}</small> : null}
                </label>

                <label>
                    Cantidad
                    <input defaultValue={item?.stock_actual ?? 0} disabled={disabled} min="0" name="stock_actual" step="0.01" type="number" />
                    {fieldError(errors, "stock_actual") ? <small className="field-error">{fieldError(errors, "stock_actual")}</small> : null}
                </label>

                <label>
                    Stock minimo
                    <input defaultValue={item?.stock_minimo ?? 0} disabled={disabled} min="0" name="stock_minimo" step="0.01" type="number" />
                    {fieldError(errors, "stock_minimo") ? <small className="field-error">{fieldError(errors, "stock_minimo")}</small> : null}
                </label>

                <label className="is-wide">
                    Descripcion
                    <textarea defaultValue={item?.descripcion || ""} disabled={disabled} name="descripcion" rows={3} />
                    {fieldError(errors, "descripcion") ? <small className="field-error">{fieldError(errors, "descripcion")}</small> : null}
                </label>
            </div>
        </FormModal>
    );
}

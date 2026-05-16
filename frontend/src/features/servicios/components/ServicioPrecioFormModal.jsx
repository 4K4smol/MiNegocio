import { FormModal } from "../../../shared/components/FormModal";
import { fieldError, metaToText } from "../utils/serviciosForms";

const dateValue = (value) => {
    if (!value) return "";
    return String(value).slice(0, 10);
};

export function ServicioPrecioFormModal({
    disabled = false,
    errors = {},
    error,
    loading = false,
    mode = "create",
    onClose,
    onSubmit,
    open,
    precio,
    tarifas = [],
}) {
    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={mode === "edit" ? "Guardar precio" : "Crear precio"}
            title={mode === "edit" ? "Editar precio" : "Nuevo precio"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label>
                    Tarifa
                    <select defaultValue={precio?.servicio_tarifa_id || ""} disabled={disabled} name="servicio_tarifa_id" required>
                        <option value="">Selecciona una tarifa</option>
                        {tarifas.map((tarifa) => (
                            <option key={tarifa.id} value={tarifa.id}>
                                {tarifa.nombre}
                            </option>
                        ))}
                    </select>
                    {fieldError(errors, "servicio_tarifa_id") ? <small className="field-error">{fieldError(errors, "servicio_tarifa_id")}</small> : null}
                </label>
                <label>
                    Precio base
                    <input defaultValue={precio?.precio_base ?? ""} disabled={disabled} min="0" name="precio_base" step="0.01" type="number" required />
                    {fieldError(errors, "precio_base") ? <small className="field-error">{fieldError(errors, "precio_base")}</small> : null}
                </label>
                <label>
                    IVA %
                    <input defaultValue={precio?.iva_porcentaje ?? 21} disabled={disabled} min="0" max="100" name="iva_porcentaje" step="0.01" type="number" />
                    {fieldError(errors, "iva_porcentaje") ? <small className="field-error">{fieldError(errors, "iva_porcentaje")}</small> : null}
                </label>
                <label>
                    Retencion %
                    <input defaultValue={precio?.retencion_porcentaje ?? ""} disabled={disabled} min="0" max="100" name="retencion_porcentaje" step="0.01" type="number" />
                    {fieldError(errors, "retencion_porcentaje") ? <small className="field-error">{fieldError(errors, "retencion_porcentaje")}</small> : null}
                </label>
                <label>
                    Moneda
                    <input defaultValue={precio?.moneda || "EUR"} disabled={disabled} maxLength={3} minLength={3} name="moneda" />
                    {fieldError(errors, "moneda") ? <small className="field-error">{fieldError(errors, "moneda")}</small> : null}
                </label>
                <label>
                    Vigente desde
                    <input defaultValue={dateValue(precio?.vigente_desde)} disabled={disabled} name="vigente_desde" type="date" />
                    {fieldError(errors, "vigente_desde") ? <small className="field-error">{fieldError(errors, "vigente_desde")}</small> : null}
                </label>
                <label>
                    Vigente hasta
                    <input defaultValue={dateValue(precio?.vigente_hasta)} disabled={disabled} name="vigente_hasta" type="date" />
                    {fieldError(errors, "vigente_hasta") ? <small className="field-error">{fieldError(errors, "vigente_hasta")}</small> : null}
                </label>
                <label className="is-wide">
                    Meta (JSON opcional)
                    <textarea defaultValue={metaToText(precio?.meta)} disabled={disabled} name="meta" rows={3} />
                    {fieldError(errors, "meta") ? <small className="field-error">{fieldError(errors, "meta")}</small> : null}
                </label>
            </div>
        </FormModal>
    );
}

import { FormModal } from "../../../shared/components/FormModal";
import { fieldError } from "../utils/serviciosForms";

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
    tipoTarifaPreseleccionado = null,
    tiposTarifa = [],
}) {
    const tarifaDefaultValue = precio?.tipo_tarifa_servicio_id || tipoTarifaPreseleccionado?.id || "";

    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={mode === "edit" ? "Guardar precio" : "Anadir precio"}
            title={tipoTarifaPreseleccionado ? `Precio - ${tipoTarifaPreseleccionado.nombre}` : (mode === "edit" ? "Editar precio" : "Nuevo precio")}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                {tipoTarifaPreseleccionado ? (
                    <input type="hidden" name="tipo_tarifa_servicio_id" value={tipoTarifaPreseleccionado.id} />
                ) : (
                    <label>
                        Tipo de tarifa
                        <select defaultValue={tarifaDefaultValue} disabled={disabled} name="tipo_tarifa_servicio_id" required>
                            <option value="">Selecciona un tipo de tarifa</option>
                            {tiposTarifa.map((tarifa) => (
                                <option key={tarifa.id} value={tarifa.id}>
                                    {tarifa.nombre}
                                </option>
                            ))}
                        </select>
                        {fieldError(errors, "tipo_tarifa_servicio_id") ? <small className="field-error">{fieldError(errors, "tipo_tarifa_servicio_id")}</small> : null}
                    </label>
                )}

                <label>
                    Precio base (EUR)
                    <input defaultValue={precio?.precio_base ?? ""} disabled={disabled} min="0" name="precio_base" placeholder="0.00" required step="0.01" type="number" />
                    {fieldError(errors, "precio_base") ? <small className="field-error">{fieldError(errors, "precio_base")}</small> : null}
                </label>

                <label>
                    IVA (%)
                    <input defaultValue={precio?.iva_porcentaje ?? 21} disabled={disabled} max="100" min="0" name="iva_porcentaje" step="0.01" type="number" />
                    {fieldError(errors, "iva_porcentaje") ? <small className="field-error">{fieldError(errors, "iva_porcentaje")}</small> : null}
                </label>

                <label>
                    Retencion (%)
                    <input defaultValue={precio?.retencion_porcentaje ?? ""} disabled={disabled} max="100" min="0" name="retencion_porcentaje" placeholder="0" step="0.01" type="number" />
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
            </div>
        </FormModal>
    );
}

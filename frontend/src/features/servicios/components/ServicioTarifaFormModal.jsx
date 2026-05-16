import { FormModal } from "../../../shared/components/FormModal";
import { fieldError } from "../utils/serviciosForms";

export function ServicioTarifaFormModal({
    disabled = false,
    errors = {},
    error,
    loading = false,
    mode = "create",
    onClose,
    onSubmit,
    open,
    tarifa,
}) {
    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={mode === "edit" ? "Guardar cambios" : "Crear tarifa"}
            title={mode === "edit" ? "Editar tarifa" : "Nueva tarifa"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label>
                    Codigo
                    <input defaultValue={tarifa?.codigo || ""} disabled={disabled} name="codigo" required />
                    {fieldError(errors, "codigo") ? <small className="field-error">{fieldError(errors, "codigo")}</small> : null}
                </label>
                <label>
                    Nombre
                    <input defaultValue={tarifa?.nombre || ""} disabled={disabled} name="nombre" required />
                    {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                </label>
                <label className="is-wide">
                    Descripcion
                    <textarea defaultValue={tarifa?.descripcion || ""} disabled={disabled} name="descripcion" rows={3} />
                    {fieldError(errors, "descripcion") ? <small className="field-error">{fieldError(errors, "descripcion")}</small> : null}
                </label>
                <label className="form-checkbox">
                    <input defaultChecked={tarifa?.es_default ?? false} disabled={disabled} name="es_default" type="checkbox" />
                    Tarifa predeterminada
                </label>
                <label className="form-checkbox">
                    <input defaultChecked={tarifa?.activo ?? true} disabled={disabled} name="activo" type="checkbox" />
                    Tarifa activa
                </label>
            </div>
        </FormModal>
    );
}

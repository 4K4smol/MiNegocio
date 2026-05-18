import { FormModal } from "../../../shared/components/FormModal";
import { SwitchInput } from "../../../shared/components/ui/SwitchInput";

const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export function UbicacionFormModal({
    disabled = false,
    error,
    errors = {},
    loading = false,
    mode = "create",
    onClose,
    onSubmit,
    open,
    ubicacion,
}) {
    const isEdit = mode === "edit";

    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={isEdit ? "Guardar ubicacion" : "Crear ubicacion"}
            title={isEdit ? "Editar ubicacion" : "Nueva ubicacion"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label>
                    Nombre
                    <input
                        defaultValue={ubicacion?.nombre || ""}
                        disabled={disabled}
                        name="nombre"
                        required
                    />
                    {fieldError(errors, "nombre") ? (
                        <small className="field-error">
                            {fieldError(errors, "nombre")}
                        </small>
                    ) : null}
                </label>
                <label className="is-wide">
                    Descripcion
                    <textarea
                        defaultValue={ubicacion?.descripcion || ""}
                        disabled={disabled}
                        name="descripcion"
                        rows={3}
                    />
                </label>
                <label className="is-wide">
                    Observaciones
                    <textarea
                        defaultValue={ubicacion?.observaciones || ""}
                        disabled={disabled}
                        name="observaciones"
                        rows={3}
                    />
                </label>
                <div className="is-wide">
                    <SwitchInput
                        defaultChecked={ubicacion?.activo ?? true}
                        disabled={disabled}
                        helpText="Controla si esta ubicacion aparece disponible para nuevos movimientos."
                        label="Ubicacion activa"
                        name="activo"
                    />
                </div>
            </div>
        </FormModal>
    );
}

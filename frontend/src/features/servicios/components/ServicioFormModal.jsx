import { FormModal } from "../../../shared/components/FormModal";
import { fieldError, metaToText } from "../utils/serviciosForms";

export function ServicioFormModal({
    categorias = [],
    disabled = false,
    errors = {},
    error,
    loading = false,
    mode = "create",
    onClose,
    onSubmit,
    open,
    servicio,
}) {
    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={mode === "edit" ? "Guardar cambios" : "Crear servicio"}
            title={mode === "edit" ? "Editar servicio" : "Nuevo servicio"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label>
                    Categoria
                    <input
                        defaultValue={servicio?.tipo_negocio || ""}
                        disabled={disabled}
                        list="servicio-categorias-options"
                        name="tipo_negocio"
                        placeholder="Limpieza"
                    />
                    <datalist id="servicio-categorias-options">
                        {categorias.map((categoria) => (
                            <option key={categoria.nombre} value={categoria.nombre} />
                        ))}
                    </datalist>
                    {fieldError(errors, "tipo_negocio") ? <small className="field-error">{fieldError(errors, "tipo_negocio")}</small> : null}
                </label>

                <label>
                    Codigo
                    <input defaultValue={servicio?.codigo || ""} disabled={disabled} name="codigo" />
                    {fieldError(errors, "codigo") ? <small className="field-error">{fieldError(errors, "codigo")}</small> : null}
                </label>

                <label className="is-wide">
                    Nombre
                    <input defaultValue={servicio?.nombre || ""} disabled={disabled} name="nombre" required />
                    {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                </label>

                <label>
                    Unidad
                    <input defaultValue={servicio?.unidad_servicio || "servicio"} disabled={disabled} name="unidad_servicio" required />
                    {fieldError(errors, "unidad_servicio") ? <small className="field-error">{fieldError(errors, "unidad_servicio")}</small> : null}
                </label>

                <label>
                    Duracion estimada (min)
                    <input
                        defaultValue={servicio?.duracion_estimada_min ?? ""}
                        disabled={disabled}
                        min="0"
                        name="duracion_estimada_min"
                        type="number"
                    />
                    {fieldError(errors, "duracion_estimada_min") ? <small className="field-error">{fieldError(errors, "duracion_estimada_min")}</small> : null}
                </label>

                <label className="is-wide">
                    Descripcion
                    <textarea defaultValue={servicio?.descripcion || ""} disabled={disabled} name="descripcion" rows={3} />
                    {fieldError(errors, "descripcion") ? <small className="field-error">{fieldError(errors, "descripcion")}</small> : null}
                </label>

                <label className="is-wide">
                    Meta (JSON opcional)
                    <textarea defaultValue={metaToText(servicio?.meta)} disabled={disabled} name="meta" rows={3} />
                    {fieldError(errors, "meta") ? <small className="field-error">{fieldError(errors, "meta")}</small> : null}
                </label>

                <label className="form-checkbox is-wide">
                    <input defaultChecked={servicio?.activo ?? true} disabled={disabled} name="activo" type="checkbox" />
                    Servicio activo
                </label>
            </div>
        </FormModal>
    );
}

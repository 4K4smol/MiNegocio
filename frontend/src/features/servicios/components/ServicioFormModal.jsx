import { FormModal } from "../../../shared/components/FormModal";
import { fieldError } from "../utils/serviciosForms";

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
    const isEdit = mode === "edit";

    return (
        <FormModal
            error={error}
            loading={loading}
            mode={mode}
            open={open}
            submitLabel={isEdit ? "Guardar cambios" : "Crear servicio"}
            title={isEdit ? "Editar servicio" : "Nuevo servicio"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="form-grid">
                <label className="is-wide">
                    Nombre del servicio
                    <input defaultValue={servicio?.nombre || ""} disabled={disabled} name="nombre" placeholder="Limpieza de cristales" required />
                    {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                </label>

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
                    <input defaultValue={servicio?.codigo || ""} disabled={disabled} name="codigo" placeholder="LIMP-001" />
                    {fieldError(errors, "codigo") ? <small className="field-error">{fieldError(errors, "codigo")}</small> : null}
                </label>

                <label className="is-wide">
                    Descripcion
                    <textarea defaultValue={servicio?.descripcion || ""} disabled={disabled} name="descripcion" placeholder="Describe el servicio..." rows={3} />
                    {fieldError(errors, "descripcion") ? <small className="field-error">{fieldError(errors, "descripcion")}</small> : null}
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
                        placeholder="60"
                        type="number"
                    />
                    {fieldError(errors, "duracion_estimada_min") ? <small className="field-error">{fieldError(errors, "duracion_estimada_min")}</small> : null}
                </label>

                {!isEdit ? (
                    <>
                        <label>
                            Precio base (€)
                            <input
                                defaultValue=""
                                disabled={disabled}
                                min="0"
                                name="precio_base"
                                placeholder="30.00"
                                step="0.01"
                                type="number"
                            />
                            {fieldError(errors, "precio_base") ? <small className="field-error">{fieldError(errors, "precio_base")}</small> : null}
                            <small className="field-hint">Se asignara a la tarifa Estandar. Podras configurar otros precios despues.</small>
                        </label>

                        <label>
                            IVA (%)
                            <input
                                defaultValue="21"
                                disabled={disabled}
                                max="100"
                                min="0"
                                name="iva_porcentaje"
                                step="0.01"
                                type="number"
                            />
                            {fieldError(errors, "iva_porcentaje") ? <small className="field-error">{fieldError(errors, "iva_porcentaje")}</small> : null}
                        </label>

                        <label>
                            Retencion (%) — opcional
                            <input
                                defaultValue=""
                                disabled={disabled}
                                max="100"
                                min="0"
                                name="retencion_porcentaje"
                                placeholder="0"
                                step="0.01"
                                type="number"
                            />
                            {fieldError(errors, "retencion_porcentaje") ? <small className="field-error">{fieldError(errors, "retencion_porcentaje")}</small> : null}
                        </label>
                    </>
                ) : null}

                <label className="form-checkbox is-wide">
                    <input defaultChecked={servicio?.activo ?? true} disabled={disabled} name="activo" type="checkbox" />
                    Servicio activo
                </label>
            </div>
        </FormModal>
    );
}

import { FormModal } from "../../../shared/components/FormModal";
import { FormSection, SwitchInput } from "../../../shared/components/ui";
import { fieldError } from "../utils/serviciosForms";

export function ServicioFormModal({
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
            size="xl"
            submitLabel={isEdit ? "Guardar cambios" : "Crear servicio"}
            title={isEdit ? "Editar servicio" : "Nuevo servicio"}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <FormSection
                description="Identifica el servicio con nombre, área y código interno."
                title="Datos básicos"
            >
                <div className="form-grid">
                    <label className="is-wide">
                        Nombre del servicio
                        <input
                            defaultValue={servicio?.nombre || ""}
                            disabled={disabled}
                            name="nombre"
                            placeholder="Limpieza de cristales"
                            required
                        />
                        {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                    </label>

                    <label>
                        Tipo o área del servicio
                        <input
                            defaultValue={servicio?.tipo_negocio || ""}
                            disabled={disabled}
                            name="tipo_negocio"
                            placeholder="Limpieza, mantenimiento, reparación..."
                        />
                        {fieldError(errors, "tipo_negocio") ? <small className="field-error">{fieldError(errors, "tipo_negocio")}</small> : null}
                    </label>

                    <label>
                        Código interno
                        <input
                            defaultValue={servicio?.codigo || ""}
                            disabled={disabled}
                            name="codigo"
                            placeholder="LIMP-001"
                        />
                        {fieldError(errors, "codigo") ? <small className="field-error">{fieldError(errors, "codigo")}</small> : null}
                    </label>

                    <label className="is-wide">
                        Descripción
                        <textarea
                            defaultValue={servicio?.descripcion || ""}
                            disabled={disabled}
                            name="descripcion"
                            placeholder="Describe el servicio..."
                            rows={3}
                        />
                        {fieldError(errors, "descripcion") ? <small className="field-error">{fieldError(errors, "descripcion")}</small> : null}
                    </label>

                    <label>
                        Duración estimada (min)
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
                </div>
            </FormSection>

            <FormSection
                description="Define cómo se contabiliza el servicio y si está disponible para nuevas órdenes."
                title="Configuración"
            >
                <div className="form-grid">
                    <label>
                        Unidad de medida
                        <input
                            defaultValue={servicio?.unidad_servicio || "servicio"}
                            disabled={disabled}
                            name="unidad_servicio"
                            required
                        />
                        {fieldError(errors, "unidad_servicio") ? <small className="field-error">{fieldError(errors, "unidad_servicio")}</small> : null}
                    </label>

                    <div>
                        <SwitchInput
                            defaultChecked={servicio?.activo ?? true}
                            disabled={disabled}
                            helpText="Un servicio inactivo no puede seleccionarse en nuevas órdenes."
                            label="Servicio activo"
                            name="activo"
                        />
                        {fieldError(errors, "activo") ? <small className="field-error">{fieldError(errors, "activo")}</small> : null}
                    </div>
                </div>
            </FormSection>
        </FormModal>
    );
}

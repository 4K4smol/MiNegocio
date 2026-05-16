const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export function ClienteForm({
    errors = {},
    initialValues = {},
    isSubmitting = false,
    onSubmit,
    submitLabel = "Guardar cliente",
    tiposCliente = [],
}) {
    // TODO: añadir localización principal cuando exista endpoint CRUD para localizaciones de cliente.
    const handleSubmit = (event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);

        onSubmit?.({
            tipo_cliente_id: Number(formData.get("tipo_cliente_id")),
            nombre: emptyToNull(formData.get("nombre")),
            apellidos: emptyToNull(formData.get("apellidos")),
            razon_social: emptyToNull(formData.get("razon_social")),
            dni_cif: emptyToNull(formData.get("dni_cif")),
            telefono: emptyToNull(formData.get("telefono")),
            email: emptyToNull(formData.get("email")),
            persona_contacto: emptyToNull(formData.get("persona_contacto")),
            notas: emptyToNull(formData.get("notas")),
            activo: formData.get("activo") === "on",
        });
    };

    return (
        <form className="form cliente-form" onSubmit={handleSubmit}>
            <label>
                Tipo de cliente
                <select
                    name="tipo_cliente_id"
                    defaultValue={initialValues.tipo_cliente_id || ""}
                    required
                >
                    <option value="">Selecciona un tipo</option>
                    {tiposCliente.map((tipo) => (
                        <option key={tipo.id} value={tipo.id}>
                            {tipo.nombre}
                        </option>
                    ))}
                </select>
                {fieldError(errors, "tipo_cliente_id") ? <small className="field-error">{fieldError(errors, "tipo_cliente_id")}</small> : null}
            </label>

            <label>
                Nombre
                <input name="nombre" defaultValue={initialValues.nombre || ""} required />
                {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
            </label>

            <label>
                Apellidos
                <input name="apellidos" defaultValue={initialValues.apellidos || ""} />
                {fieldError(errors, "apellidos") ? <small className="field-error">{fieldError(errors, "apellidos")}</small> : null}
            </label>

            <label>
                Razón social
                <input name="razon_social" defaultValue={initialValues.razon_social || ""} />
                {fieldError(errors, "razon_social") ? <small className="field-error">{fieldError(errors, "razon_social")}</small> : null}
            </label>

            <label>
                DNI/CIF
                <input name="dni_cif" defaultValue={initialValues.dni_cif || ""} required />
                {fieldError(errors, "dni_cif") ? <small className="field-error">{fieldError(errors, "dni_cif")}</small> : null}
            </label>

            <label>
                Teléfono
                <input name="telefono" defaultValue={initialValues.telefono || ""} />
                {fieldError(errors, "telefono") ? <small className="field-error">{fieldError(errors, "telefono")}</small> : null}
            </label>

            <label>
                Email
                <input name="email" type="email" defaultValue={initialValues.email || ""} />
                {fieldError(errors, "email") ? <small className="field-error">{fieldError(errors, "email")}</small> : null}
            </label>

            <label>
                Persona de contacto
                <input name="persona_contacto" defaultValue={initialValues.persona_contacto || ""} />
                {fieldError(errors, "persona_contacto") ? <small className="field-error">{fieldError(errors, "persona_contacto")}</small> : null}
            </label>

            <label className="is-wide">
                Notas
                <textarea name="notas" defaultValue={initialValues.notas || ""} rows={4} />
                {fieldError(errors, "notas") ? <small className="field-error">{fieldError(errors, "notas")}</small> : null}
            </label>

            <label className="form-checkbox is-wide">
                <input
                    name="activo"
                    type="checkbox"
                    defaultChecked={initialValues.activo ?? true}
                />
                Cliente activo
            </label>

            <div className="form-actions is-wide">
                <button className="button" type="submit" disabled={isSubmitting}>
                    {isSubmitting ? "Guardando..." : submitLabel}
                </button>
            </div>
        </form>
    );
}

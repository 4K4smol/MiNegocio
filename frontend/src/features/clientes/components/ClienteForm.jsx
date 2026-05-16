const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export function ClienteForm({
    disabled = false,
    errors = {},
    initialValues = {},
    tiposCliente = [],
}) {
    // TODO: anadir localizacion principal cuando exista endpoint CRUD para localizaciones de cliente.
    return (
        <div className="cliente-form form-grid">
            <label>
                Tipo de cliente
                <select
                    defaultValue={initialValues.tipo_cliente_id || ""}
                    disabled={disabled}
                    name="tipo_cliente_id"
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
                <input defaultValue={initialValues.nombre || ""} disabled={disabled} name="nombre" required />
                {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
            </label>

            <label>
                Apellidos
                <input defaultValue={initialValues.apellidos || ""} disabled={disabled} name="apellidos" />
                {fieldError(errors, "apellidos") ? <small className="field-error">{fieldError(errors, "apellidos")}</small> : null}
            </label>

            <label>
                Razon social
                <input defaultValue={initialValues.razon_social || ""} disabled={disabled} name="razon_social" />
                {fieldError(errors, "razon_social") ? <small className="field-error">{fieldError(errors, "razon_social")}</small> : null}
            </label>

            <label>
                DNI/CIF
                <input defaultValue={initialValues.dni_cif || ""} disabled={disabled} name="dni_cif" required />
                {fieldError(errors, "dni_cif") ? <small className="field-error">{fieldError(errors, "dni_cif")}</small> : null}
            </label>

            <label>
                Telefono
                <input defaultValue={initialValues.telefono || ""} disabled={disabled} name="telefono" />
                {fieldError(errors, "telefono") ? <small className="field-error">{fieldError(errors, "telefono")}</small> : null}
            </label>

            <label>
                Email
                <input defaultValue={initialValues.email || ""} disabled={disabled} name="email" type="email" />
                {fieldError(errors, "email") ? <small className="field-error">{fieldError(errors, "email")}</small> : null}
            </label>

            <label>
                Persona de contacto
                <input defaultValue={initialValues.persona_contacto || ""} disabled={disabled} name="persona_contacto" />
                {fieldError(errors, "persona_contacto") ? <small className="field-error">{fieldError(errors, "persona_contacto")}</small> : null}
            </label>

            <label className="is-wide">
                Notas
                <textarea defaultValue={initialValues.notas || ""} disabled={disabled} name="notas" rows={4} />
                {fieldError(errors, "notas") ? <small className="field-error">{fieldError(errors, "notas")}</small> : null}
            </label>

            <label className="form-checkbox is-wide">
                <input
                    defaultChecked={initialValues.activo ?? true}
                    disabled={disabled}
                    name="activo"
                    type="checkbox"
                />
                Cliente activo
            </label>
        </div>
    );
}

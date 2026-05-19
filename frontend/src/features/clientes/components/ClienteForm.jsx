import { useState } from "react";

const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

const normalizeCode = (value) => String(value || "").toLowerCase();

export function ClienteForm({
    disabled = false,
    errors = {},
    initialValues = {},
    tiposCliente = [],
}) {
    const [selectedTipoClienteId, setSelectedTipoClienteId] = useState(String(initialValues.tipo_cliente_id || ""));
    const selectedTipoCliente = tiposCliente.find((tipo) => String(tipo.id) === selectedTipoClienteId);
    const selectedTipoCodigo = normalizeCode(selectedTipoCliente?.codigo);
    const isParticular = selectedTipoCodigo === "particular";
    const isEmpresa = selectedTipoCodigo === "empresa";
    const showCommonFields = !selectedTipoCodigo;

    return (
        <div className="cliente-form form-grid">
            <input name="tipo_cliente_codigo" readOnly type="hidden" value={selectedTipoCodigo} />

            <label>
                Tipo de cliente
                <select
                    disabled={disabled}
                    name="tipo_cliente_id"
                    required
                    value={selectedTipoClienteId}
                    onChange={(event) => setSelectedTipoClienteId(event.target.value)}
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

            {(isParticular || showCommonFields) ? (
                <>
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
                        DNI/NIE
                        <input defaultValue={initialValues.dni_cif || ""} disabled={disabled} name="dni_cif" required />
                        {fieldError(errors, "dni_cif") ? <small className="field-error">{fieldError(errors, "dni_cif")}</small> : null}
                    </label>
                </>
            ) : null}

            {isEmpresa ? (
                <>
                    <label>
                        Razon social
                        <input defaultValue={initialValues.razon_social || ""} disabled={disabled} name="razon_social" required />
                        {fieldError(errors, "razon_social") ? <small className="field-error">{fieldError(errors, "razon_social")}</small> : null}
                    </label>

                    <label>
                        Nombre comercial / alias
                        <input defaultValue={initialValues.nombre || ""} disabled={disabled} name="nombre" required />
                        {fieldError(errors, "nombre") ? <small className="field-error">{fieldError(errors, "nombre")}</small> : null}
                    </label>

                    <label>
                        CIF
                        <input defaultValue={initialValues.dni_cif || ""} disabled={disabled} name="dni_cif" required />
                        {fieldError(errors, "dni_cif") ? <small className="field-error">{fieldError(errors, "dni_cif")}</small> : null}
                    </label>

                    <label>
                        Persona de contacto
                        <input defaultValue={initialValues.persona_contacto || ""} disabled={disabled} name="persona_contacto" />
                        {fieldError(errors, "persona_contacto") ? <small className="field-error">{fieldError(errors, "persona_contacto")}</small> : null}
                    </label>
                </>
            ) : null}

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

            <label className="is-wide">
                Notas
                <textarea defaultValue={initialValues.notas || ""} disabled={disabled} name="notas" rows={4} />
                {fieldError(errors, "notas") ? <small className="field-error">{fieldError(errors, "notas")}</small> : null}
            </label>

            <label className="is-wide">
                Direccion
                <input defaultValue={initialValues.direccion || ""} disabled={disabled} name="direccion" />
            </label>

            <label>
                Ciudad
                <input defaultValue={initialValues.ciudad || ""} disabled={disabled} name="ciudad" />
            </label>

            <label>
                Codigo postal
                <input defaultValue={initialValues.codigo_postal || ""} disabled={disabled} name="codigo_postal" />
            </label>

            <label>
                Provincia
                <input defaultValue={initialValues.provincia || ""} disabled={disabled} name="provincia" />
            </label>

            <label>
                Pais
                <input defaultValue={initialValues.pais || "España"} disabled={disabled} name="pais" />
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

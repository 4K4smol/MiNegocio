const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

export const clientePayloadFromForm = (form) => {
    const formData = new FormData(form);
    const tipoClienteId = formData.get("tipo_cliente_id");
    const tipoClienteCodigo = String(formData.get("tipo_cliente_codigo") || "").toLowerCase();
    const isParticular = tipoClienteCodigo === "particular";
    const isEmpresa = tipoClienteCodigo === "empresa";

    return {
        tipo_cliente_id: tipoClienteId ? Number(tipoClienteId) : null,
        nombre: emptyToNull(formData.get("nombre")),
        apellidos: isEmpresa ? null : emptyToNull(formData.get("apellidos")),
        razon_social: isParticular ? null : emptyToNull(formData.get("razon_social")),
        dni_cif: emptyToNull(formData.get("dni_cif")),
        telefono: emptyToNull(formData.get("telefono")),
        email: emptyToNull(formData.get("email")),
        persona_contacto: isParticular ? null : emptyToNull(formData.get("persona_contacto")),
        notas: emptyToNull(formData.get("notas")),
        activo: formData.get("activo") === "on",
    };
};

export const getClienteDisplayName = (cliente) =>
    cliente.razon_social || [cliente.nombre, cliente.apellidos].filter(Boolean).join(" ");

export const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

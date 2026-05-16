const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

export const clientePayloadFromForm = (form) => {
    const formData = new FormData(form);
    const tipoClienteId = formData.get("tipo_cliente_id");

    return {
        tipo_cliente_id: tipoClienteId ? Number(tipoClienteId) : null,
        nombre: emptyToNull(formData.get("nombre")),
        apellidos: emptyToNull(formData.get("apellidos")),
        razon_social: emptyToNull(formData.get("razon_social")),
        dni_cif: emptyToNull(formData.get("dni_cif")),
        telefono: emptyToNull(formData.get("telefono")),
        email: emptyToNull(formData.get("email")),
        persona_contacto: emptyToNull(formData.get("persona_contacto")),
        notas: emptyToNull(formData.get("notas")),
        activo: formData.get("activo") === "on",
    };
};

export const getClienteDisplayName = (cliente) =>
    cliente.razon_social || [cliente.nombre, cliente.apellidos].filter(Boolean).join(" ");

export const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

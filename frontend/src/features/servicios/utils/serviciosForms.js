const emptyToNull = (value) => {
    const trimmed = String(value ?? "").trim();
    return trimmed === "" ? null : trimmed;
};

const numberOrNull = (value) => {
    const normalized = emptyToNull(value);
    return normalized === null ? null : Number(normalized);
};

const metaFromForm = (formData) => {
    const raw = emptyToNull(formData.get("meta"));
    if (raw === null) return null;

    try {
        return JSON.parse(raw);
    } catch {
        throw new Error("El campo meta debe contener JSON valido.");
    }
};

export const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

export const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export const servicioPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        tipo_negocio: emptyToNull(formData.get("tipo_negocio")),
        codigo: emptyToNull(formData.get("codigo")),
        nombre: emptyToNull(formData.get("nombre")),
        descripcion: emptyToNull(formData.get("descripcion")),
        unidad_servicio: emptyToNull(formData.get("unidad_servicio")),
        duracion_estimada_min: numberOrNull(formData.get("duracion_estimada_min")),
        activo: formData.get("activo") === "on",
        precio_base: numberOrNull(formData.get("precio_base")),
        iva_porcentaje: numberOrNull(formData.get("iva_porcentaje")),
        retencion_porcentaje: numberOrNull(formData.get("retencion_porcentaje")),
        moneda: emptyToNull(formData.get("moneda")) || "EUR",
    };
};

export const servicioTarifaPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        codigo: emptyToNull(formData.get("codigo")),
        nombre: emptyToNull(formData.get("nombre")),
        descripcion: emptyToNull(formData.get("descripcion")),
        es_default: formData.get("es_default") === "on",
        activo: formData.get("activo") === "on",
    };
};

export const servicioPrecioPayloadFromForm = (form) => {
    const formData = new FormData(form);

    return {
        servicio_tarifa_id: numberOrNull(formData.get("servicio_tarifa_id")),
        precio_base: numberOrNull(formData.get("precio_base")),
        iva_porcentaje: numberOrNull(formData.get("iva_porcentaje")),
        retencion_porcentaje: numberOrNull(formData.get("retencion_porcentaje")),
        moneda: emptyToNull(formData.get("moneda")) || "EUR",
        vigente_desde: emptyToNull(formData.get("vigente_desde")),
        vigente_hasta: emptyToNull(formData.get("vigente_hasta")),
        meta: metaFromForm(formData),
    };
};

export const metaToText = (value) => {
    if (!value) return "";
    if (typeof value === "string") return value;
    return JSON.stringify(value, null, 2);
};

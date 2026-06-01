export const clienteLabel = (cliente) =>
    cliente?.razon_social ||
    [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") ||
    cliente?.dni_cif ||
    `Cliente ${cliente?.id}`;

export const clienteLocation = (cliente) =>
    [
        cliente?.direccion,
        cliente?.ciudad,
        cliente?.provincia,
        cliente?.codigo_postal,
    ]
        .filter(Boolean)
        .join(", ");

export const clienteSearchText = (cliente) =>
    [
        clienteLabel(cliente),
        cliente?.razon_social,
        cliente?.nombre,
        cliente?.apellidos,
        cliente?.dni_cif,
        cliente?.email,
        cliente?.telefono,
        clienteLocation(cliente),
    ]
        .filter(Boolean)
        .join(" ");

export const formatOrdenDateTime = (form) => {
    if (!form?.fecha) return "Sin fecha prevista";

    const time = [
        form.hora_inicio ? `de ${form.hora_inicio}` : "",
        form.hora_fin ? `a ${form.hora_fin}` : "",
    ]
        .filter(Boolean)
        .join(" ");

    return `${form.fecha}${time ? ` ${time}` : ""}`;
};

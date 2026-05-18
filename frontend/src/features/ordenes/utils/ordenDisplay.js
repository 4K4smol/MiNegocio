export const clienteLabel = (cliente) =>
    cliente?.razon_social ||
    [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") ||
    cliente?.dni_cif ||
    `Cliente ${cliente?.id}`;

export const localizacionLabel = (localizacion) =>
    localizacion?.nombre ||
    localizacion?.nombre_localizacion ||
    localizacion?.direccion_completa ||
    localizacion?.direccion ||
    `Localizacion ${localizacion?.id}`;

export const clienteLabel = (cliente) =>
    cliente?.razon_social ||
    [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") ||
    cliente?.dni_cif ||
    `Cliente ${cliente?.id}`;


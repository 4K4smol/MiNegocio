const getRoleName = (value) => {
    if (!value) return "";
    if (typeof value === "string") return value;
    return value.nombre || value.name || "";
};

const resolveRoles = (usuario, session) =>
    [
        usuario?.role,
        usuario?.rol,
        usuario?.tipo,
        session?.role,
        session?.rol,
        session?.tipo,
    ]
        .map(getRoleName)
        .filter(Boolean);

export const hasRole = (usuario, session, allowedRoles) => {
    const roles = resolveRoles(usuario, session);
    return allowedRoles.some((role) => roles.includes(role));
};

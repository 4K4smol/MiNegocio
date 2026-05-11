const estadoClassMap = {
    pendiente: "estado-pendiente",
    "en revision": "estado-revision",
    aprobada: "estado-aprobada",
    activa: "estado-aprobada",
    validado: "estado-aprobada",
    aprobado: "estado-aprobada",
    rechazada: "estado-rechazada",
    inactiva: "estado-rechazada",
    rechazado: "estado-rechazada",
    "requiere subsanacion": "estado-subsanacion",
};

const normalizeEstado = (estado = "") =>
    estado
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

export function EstadoBadge({ estado }) {
    const normalized = normalizeEstado(estado);
    const className = estadoClassMap[normalized] || "estado-neutral";

    return <span className={`estado-badge ${className}`}>{estado}</span>;
}

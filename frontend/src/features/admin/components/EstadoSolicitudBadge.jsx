const LABELS = {
    pendiente: "Pendiente",
    en_revision: "En revisión",
    subsanacion: "Subsanación",
    aprobada: "Aprobada",
    rechazada: "Rechazada",
    activa: "Activo",
    inactiva: "No activo",
};

const CLASS_NAMES = {
    pendiente: "estado-pendiente",
    en_revision: "estado-revision",
    subsanacion: "estado-subsanacion",
    aprobada: "estado-aprobada",
    rechazada: "estado-rechazada",
    activa: "estado-aprobada",
    inactiva: "estado-rechazada",
};

export function EstadoSolicitudBadge({ estado = "pendiente" }) {
    const normalized = String(estado || "pendiente").toLowerCase();

    return (
        <span className={`estado-badge ${CLASS_NAMES[normalized] || "estado-neutral"}`}>
            {LABELS[normalized] || estado}
        </span>
    );
}

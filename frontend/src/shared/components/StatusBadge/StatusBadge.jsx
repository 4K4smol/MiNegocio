const STATUS_CONFIG = {
    activa: { label: "Activo", tone: "success", legacy: "estado-aprobada" },
    activo: { label: "Activo", tone: "success", legacy: "estado-aprobada" },
    aprobada: { label: "Aprobada", tone: "success", legacy: "estado-aprobada" },
    aprobado: { label: "Aprobado", tone: "success", legacy: "estado-aprobada" },
    completada: { label: "Completada", tone: "success", legacy: "estado-aprobada" },
    pagada: { label: "Pagada", tone: "success", legacy: "estado-aprobada" },
    validado: { label: "Validado", tone: "success", legacy: "estado-aprobada" },
    en_revision: { label: "En revisión", tone: "info", legacy: "estado-revision" },
    en_proceso: { label: "En proceso", tone: "info", legacy: "estado-revision" },
    en_progreso: { label: "En progreso", tone: "info", legacy: "estado-revision" },
    facturada: { label: "Facturada", tone: "info", legacy: "estado-revision" },
    informacion: { label: "Información", tone: "info", legacy: "estado-revision" },
    programada: { label: "Programada", tone: "info", legacy: "estado-revision" },
    pendiente: { label: "Pendiente", tone: "warning", legacy: "estado-pendiente" },
    subsanacion: { label: "Subsanación", tone: "warning", legacy: "estado-subsanacion" },
    requiere_subsanacion: {
        label: "Requiere subsanación",
        tone: "warning",
        legacy: "estado-subsanacion",
    },
    cancelada: { label: "Cancelada", tone: "danger", legacy: "estado-rechazada" },
    inactiva: { label: "No activo", tone: "danger", legacy: "estado-rechazada" },
    inactivo: { label: "No activo", tone: "danger", legacy: "estado-rechazada" },
    rechazada: { label: "Rechazada", tone: "danger", legacy: "estado-rechazada" },
    rechazado: { label: "Rechazado", tone: "danger", legacy: "estado-rechazada" },
    anulada: { label: "Anulada", tone: "danger", legacy: "estado-rechazada" },
    borrador: { label: "Borrador", tone: "neutral", legacy: "estado-neutral" },
    emitida: { label: "Emitida", tone: "info", legacy: "estado-revision" },
    rectificada: { label: "Rectificada", tone: "warning", legacy: "estado-subsanacion" },
};

const normalizeStatus = (status = "") =>
    String(status)
        .toLowerCase()
        .trim()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[\s-]+/g, "_");

const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function StatusBadge({ children, className = "", label, status = "pendiente" }) {
    const normalized = normalizeStatus(status);
    const config = STATUS_CONFIG[normalized] || {
        label: label || status,
        tone: "neutral",
        legacy: "estado-neutral",
    };

    return (
        <span
            className={joinClasses(
                "status-badge",
                "estado-badge",
                `status-badge--${config.tone}`,
                config.legacy,
                className,
            )}
        >
            {children || label || config.label}
        </span>
    );
}

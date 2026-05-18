const STATUS_LABELS = {
    cancelada: "Cancelada",
    completada: "Completada",
    facturada: "Facturada",
    pendiente: "Pendiente",
    programada: "Programada",
};

export function OrdenCalendarEvent({ event, onClick }) {
    const status = event.status || "programada";

    return (
        <button className={`calendar-order-badge calendar-order-badge--${status}`} type="button" onClick={() => onClick?.(event)}>
            <strong>{event.title}</strong>
            <span>{STATUS_LABELS[status] || status}</span>
        </button>
    );
}


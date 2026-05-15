import { Link } from "react-router-dom";

const STATUS_LABELS = {
    cancelada: "Cancelada",
    completada: "Completada",
    facturada: "Facturada",
    pendiente: "Pendiente",
    programada: "Programada",
};

export function CalendarOrderBadge({ order }) {
    const status = order.status || "programada";
    const to = order.id ? `/app/ordenes-trabajo/${order.id}` : "/app/ordenes-trabajo";

    return (
        <Link className={`calendar-order-badge calendar-order-badge--${status}`} to={to}>
            <strong>{order.title}</strong>
            <span>{STATUS_LABELS[status] || status}</span>
        </Link>
    );
}

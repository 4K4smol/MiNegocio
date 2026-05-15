import { Link } from "react-router-dom";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { formatDate } from "../../../shared/utils/formatters";

const getOrderDate = (order) =>
    order.fechas?.programada_inicio ||
    order.fecha_programada_inicio ||
    order.fechas?.cierre ||
    order.fecha_cierre ||
    order.fechas?.apertura;

export function OrderListCard({ emptyText, orders = [], title }) {
    return (
        <article className="dashboard-orders-card">
            <header>
                <h2>{title}</h2>
            </header>
            {orders.length ? (
                <div className="dashboard-orders-list">
                    {orders.map((order) => {
                        const status = order.estado || order.estado_codigo || "pendiente";

                        return (
                            <Link
                                className="dashboard-order-row"
                                key={order.id}
                                to={`/app/ordenes-trabajo/${order.id}`}
                            >
                                <div>
                                    <strong>{order.numero || `Orden ${order.id}`}</strong>
                                    <span>
                                        {order.cliente?.nombre ||
                                            order.cliente?.razon_social ||
                                            "Sin cliente"}
                                    </span>
                                </div>
                                <div>
                                    <StatusBadge status={status} />
                                    <small>{formatDate(getOrderDate(order))}</small>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            ) : (
                <p className="dashboard-orders-empty">{emptyText}</p>
            )}
        </article>
    );
}

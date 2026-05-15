const completedStatuses = ["completada", "facturada"];

const getCompletionDate = (order) =>
    order.fechas?.cierre ||
    order.fecha_cierre ||
    order.fechas?.fin_real ||
    order.fecha_fin_real ||
    order.updated_at ||
    "";

export function getRecentCompletedOrders(orders = []) {
    return orders
        .filter((order) =>
            completedStatuses.includes(String(order.estado || order.estado_codigo || "").toLowerCase()),
        )
        .sort((a, b) => new Date(getCompletionDate(b)) - new Date(getCompletionDate(a)))
        .slice(0, 5);
}

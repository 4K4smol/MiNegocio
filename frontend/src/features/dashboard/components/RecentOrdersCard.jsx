import { OrderListCard } from "./OrderListCard";

export function RecentOrdersCard({ orders = [] }) {
    return (
        <OrderListCard
            emptyText="No hay órdenes completadas recientemente."
            orders={orders}
            title="Órdenes recientes"
        />
    );
}

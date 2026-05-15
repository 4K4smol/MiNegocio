import { OrderListCard } from "./OrderListCard";

export function UpcomingOrdersCard({ orders = [] }) {
    return (
        <OrderListCard
            emptyText="No hay órdenes programadas."
            orders={orders}
            title="Próximas órdenes"
        />
    );
}

import { OrderListCard } from "./OrderListCard";

export function UpcomingOrdersCard({ orders = [] }) {
    return (
        <OrderListCard
            emptyText="No hay órdenes pendientes programadas."
            orders={orders}
            title="Pendientes y próximas"
        />
    );
}

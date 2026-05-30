import { CalendarOrderBadge } from "./CalendarOrderBadge";
import { toDateKey } from "./calendarUtils";

export function CalendarDayCell({ date, isCurrentMonth, maxVisibleOrders, onSelectOrder, orders = [], todayKey }) {
    const dateKey = toDateKey(date);
    const isToday = dateKey === todayKey;
    const visibleOrders = Number.isInteger(maxVisibleOrders) ? orders.slice(0, maxVisibleOrders) : orders;
    const hiddenOrdersCount = orders.length - visibleOrders.length;

    return (
        <article
            className={[
                "calendar-day-cell",
                !isCurrentMonth ? "is-outside-month" : "",
                isToday ? "is-today" : "",
                orders.length ? "has-orders" : "",
            ]
                .filter(Boolean)
                .join(" ")}
        >
            <span className="calendar-day-cell__number">{date.getDate()}</span>
            <div className="calendar-day-cell__orders">
                {visibleOrders.map((order) => (
                    <CalendarOrderBadge key={`${order.id}-${order.title}`} order={order} onClick={onSelectOrder} />
                ))}
                {hiddenOrdersCount > 0 ? (
                    <span className="calendar-day-cell__more">+{hiddenOrdersCount}</span>
                ) : null}
            </div>
        </article>
    );
}

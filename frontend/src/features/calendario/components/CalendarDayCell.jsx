import { CalendarOrderBadge } from "./CalendarOrderBadge";
import { toDateKey } from "./calendarUtils";

export function CalendarDayCell({ date, isCurrentMonth, onSelectOrder, orders = [], todayKey }) {
    const dateKey = toDateKey(date);
    const isToday = dateKey === todayKey;

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
                {orders.map((order) => (
                    <CalendarOrderBadge key={`${order.id}-${order.title}`} order={order} onClick={onSelectOrder} />
                ))}
            </div>
        </article>
    );
}

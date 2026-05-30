import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import {
    formatMonthLabel,
    getCalendarDays,
    groupOrdersByDate,
    toDateKey,
    WEEK_DAYS,
} from "./calendarUtils";
import { CalendarDayCell } from "./CalendarDayCell";
import "../styles/calendario.css";

export function CalendarMonth({
    events = [],
    headerActions = null,
    maxVisibleOrders,
    monthDate,
    onNextMonth,
    onPreviousMonth,
    onSelectOrder,
    variant = "default",
}) {
    const days = getCalendarDays(monthDate);
    const currentMonth = monthDate.getMonth();
    const ordersByDate = groupOrdersByDate(events);
    const todayKey = toDateKey(new Date());
    const hasOrders = Object.keys(ordersByDate).length > 0;

    return (
        <section className={`calendar-month-card calendar-month-card--${variant}`}>
            <header className="calendar-month-header">
                <div className="calendar-month-title">
                    <span className="calendar-month-title__icon">
                        <AppIcon icon={appIcons.calendario} size={22} />
                    </span>
                    <div>
                        <span className="eyebrow">Calendario</span>
                        <h2>Calendario de órdenes</h2>
                    </div>
                </div>

                <div className="calendar-month-controls">
                    <button
                        aria-label="Mes anterior"
                        className="button button-ghost calendar-month-nav"
                        type="button"
                        onClick={onPreviousMonth}
                    >
                        <AppIcon icon={appIcons.anterior} size={18} />
                    </button>
                    <strong>{formatMonthLabel(monthDate)}</strong>
                    <button
                        aria-label="Mes siguiente"
                        className="button button-ghost calendar-month-nav"
                        type="button"
                        onClick={onNextMonth}
                    >
                        <AppIcon icon={appIcons.siguiente} size={18} />
                    </button>
                </div>

                {headerActions ? (
                    <div className="calendar-month-actions">
                        {headerActions}
                    </div>
                ) : null}
            </header>

            <div className="calendar-month-grid" aria-label="Calendario mensual de órdenes">
                {WEEK_DAYS.map((day) => (
                    <span className="calendar-month-weekday" key={day}>
                        {day}
                    </span>
                ))}

                {days.map((day) => {
                    const dateKey = toDateKey(day);
                    return (
                        <CalendarDayCell
                            date={day}
                            isCurrentMonth={day.getMonth() === currentMonth}
                            key={dateKey}
                            maxVisibleOrders={maxVisibleOrders}
                            onSelectOrder={onSelectOrder}
                            orders={ordersByDate[dateKey] || []}
                            todayKey={todayKey}
                        />
                    );
                })}
            </div>

            {!hasOrders ? (
                <p className="calendar-month-empty">No hay órdenes programadas en este mes.</p>
            ) : null}
        </section>
    );
}

import { CalendarMonth } from "./CalendarMonth";

export function CalendarioOrdenes({ events, monthDate, onNextMonth, onPreviousMonth, onSelectOrden }) {
    return (
        <CalendarMonth
            events={events}
            monthDate={monthDate}
            onNextMonth={onNextMonth}
            onPreviousMonth={onPreviousMonth}
            onSelectOrder={onSelectOrden}
        />
    );
}


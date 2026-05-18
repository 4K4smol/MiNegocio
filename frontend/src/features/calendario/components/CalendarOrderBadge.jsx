import { OrdenCalendarEvent } from "./OrdenCalendarEvent";

export function CalendarOrderBadge({ onClick, order }) {
    return <OrdenCalendarEvent event={order} onClick={onClick} />;
}

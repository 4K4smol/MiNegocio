export const WEEK_DAYS = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

export const toDateKey = (value) => {
    if (!value) return "";
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return "";

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

export const formatMonthLabel = (date) =>
    new Intl.DateTimeFormat("es-ES", {
        month: "long",
        year: "numeric",
    }).format(date);

const startOfCalendar = (date) => {
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const weekday = (firstDay.getDay() + 6) % 7;
    const start = new Date(firstDay);
    start.setDate(firstDay.getDate() - weekday);
    return start;
};

export const getCalendarDays = (date) => {
    const start = startOfCalendar(date);

    return Array.from({ length: 42 }, (_, index) => {
        const day = new Date(start);
        day.setDate(start.getDate() + index);
        return day;
    });
};

export const getMonthRange = (date) => {
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    return {
        desde: toDateKey(firstDay),
        hasta: toDateKey(lastDay),
    };
};

export const normalizeCalendarOrder = (event) => {
    const order = event.orden_trabajo || event.ordenTrabajo || event;
    const status =
        event.estado_codigo ||
        order.estado ||
        order.estado_codigo ||
        "programada";
    const start =
        event.inicio ||
        order.fechas?.programada_inicio ||
        order.fecha_programada_inicio ||
        order.fecha_programada ||
        order.fechas?.apertura;

    const normalizedStatus = String(status)
        .toLowerCase()
        .trim()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[\s-]+/g, "_");

    return {
        client:
            order.cliente?.nombre ||
            order.cliente?.razon_social ||
            event.cliente?.nombre ||
            "Sin cliente",
        dateKey: toDateKey(start),
        id: order.id || event.orden_trabajo_id || event.id,
        status: normalizedStatus === "programado" ? "programada" : normalizedStatus,
        title: event.titulo || order.numero || "Orden de trabajo",
    };
};

export const groupOrdersByDate = (events = []) =>
    events
        .map(normalizeCalendarOrder)
        .filter((event) => event.dateKey)
        .reduce((acc, event) => {
            acc[event.dateKey] = [...(acc[event.dateKey] || []), event];
            return acc;
        }, {});

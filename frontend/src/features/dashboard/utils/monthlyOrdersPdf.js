import { formatMonthLabel, normalizeCalendarOrder } from "../../calendario/components/calendarUtils";

const CLOSED_STATUSES = new Set(["cancelada", "completada", "facturada"]);

const escapeHtml = (value) =>
    String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

const formatDate = (value) => {
    if (!value) return "Sin fecha";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "Sin fecha";

    return new Intl.DateTimeFormat("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(date);
};

const pendingMonthlyOrders = (events) => {
    const ordersById = new Map();

    events
        .map(normalizeCalendarOrder)
        .filter((order) => order.id && !CLOSED_STATUSES.has(order.status))
        .forEach((order) => {
            if (!ordersById.has(order.id)) {
                ordersById.set(order.id, order);
            }
        });

    return [...ordersById.values()].sort((a, b) => {
        const dateA = new Date(a.start || a.dateKey).getTime();
        const dateB = new Date(b.start || b.dateKey).getTime();

        return (Number.isNaN(dateA) ? 0 : dateA) - (Number.isNaN(dateB) ? 0 : dateB);
    });
};

const renderRows = (orders) => {
    if (!orders.length) {
        return `
            <tr>
                <td class="empty" colspan="4">No hay ordenes del mes sin completar.</td>
            </tr>
        `;
    }

    return orders
        .map((order) => `
            <tr>
                <td>${escapeHtml(formatDate(order.start || order.dateKey))}</td>
                <td>${escapeHtml(order.title)}</td>
                <td>${escapeHtml(order.client)}</td>
                <td>${escapeHtml(order.status)}</td>
            </tr>
        `)
        .join("");
};

const buildPrintableHtml = ({ events, monthDate }) => {
    const orders = pendingMonthlyOrders(events);
    const monthLabel = formatMonthLabel(monthDate);

    return `
        <!doctype html>
        <html lang="es">
            <head>
                <meta charset="utf-8" />
                <title>Ordenes pendientes - ${escapeHtml(monthLabel)}</title>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        color: #111827;
                        font-family: Arial, sans-serif;
                        margin: 32px;
                    }
                    header {
                        border-bottom: 2px solid #6d28d9;
                        margin-bottom: 24px;
                        padding-bottom: 16px;
                    }
                    h1 {
                        font-size: 24px;
                        margin: 0 0 6px;
                    }
                    p {
                        color: #4b5563;
                        margin: 0;
                    }
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    th,
                    td {
                        border-bottom: 1px solid #e5e7eb;
                        padding: 10px 8px;
                        text-align: left;
                        vertical-align: top;
                    }
                    th {
                        background: #f5f3ff;
                        color: #4c1d95;
                        font-size: 12px;
                        text-transform: uppercase;
                    }
                    .summary {
                        font-weight: 700;
                        margin-bottom: 16px;
                    }
                    .empty {
                        color: #6b7280;
                        font-weight: 700;
                        padding: 24px 8px;
                        text-align: center;
                    }
                    @media print {
                        body { margin: 18mm; }
                    }
                </style>
            </head>
            <body>
                <header>
                    <h1>Ordenes del mes sin completar</h1>
                    <p>${escapeHtml(monthLabel)}</p>
                </header>
                <p class="summary">Total: ${orders.length}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${renderRows(orders)}
                    </tbody>
                </table>
            </body>
        </html>
    `;
};

export const printMonthlyPendingOrders = ({ events, monthDate }) => {
    const printWindow = window.open("", "_blank", "width=960,height=720");

    if (!printWindow) {
        window.print();
        return;
    }

    printWindow.document.open();
    printWindow.document.write(buildPrintableHtml({ events, monthDate }));
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
};

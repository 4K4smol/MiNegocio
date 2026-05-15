const orders = [
    { client: "Taller Norte", service: "Revisión mensual", status: "Completada" },
    { client: "Clínica Alba", service: "Mantenimiento", status: "En progreso" },
    { client: "Estudio Luma", service: "Instalación", status: "Pendiente" },
];

const days = ["L", "M", "X", "J", "V", "S", "D"];

export function DashboardPreview() {
    return (
        <aside className="dashboard-preview" aria-label="Vista previa del panel">
            <div className="preview-header">
                <div>
                    <span>Panel operativo</span>
                    <strong>Mayo 2026</strong>
                </div>
                <span className="preview-pill">CRM activo</span>
            </div>

            <div className="preview-grid">
                <div className="preview-card calendar-card">
                    <div className="preview-card-title">Calendario</div>
                    <div className="mini-calendar">
                        {days.map((day) => (
                            <span key={day} className="calendar-day-label">
                                {day}
                            </span>
                        ))}
                        {Array.from({ length: 21 }, (_, index) => (
                            <span
                                className={
                                    [3, 9, 14].includes(index)
                                        ? "calendar-day calendar-day-active"
                                        : "calendar-day"
                                }
                                key={index}
                            >
                                {index + 1}
                            </span>
                        ))}
                    </div>
                </div>

                <div className="preview-card metrics-card">
                    <div>
                        <span>Facturas emitidas</span>
                        <strong>24</strong>
                    </div>
                    <div>
                        <span>Stock bajo</span>
                        <strong>6</strong>
                    </div>
                </div>
            </div>

            <div className="preview-card">
                <div className="preview-card-title">Órdenes recientes</div>
                <div className="orders-table">
                    {orders.map((order) => (
                        <div className="order-row" key={order.client}>
                            <span>{order.client}</span>
                            <span>{order.service}</span>
                            <span
                                className={`status-badge status-${order.status
                                    .toLowerCase()
                                    .replace(" ", "-")}`}
                            >
                                {order.status}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </aside>
    );
}

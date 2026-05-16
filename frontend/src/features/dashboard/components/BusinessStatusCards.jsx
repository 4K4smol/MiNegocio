const statusItems = [
    {
        key: "clientes_activos",
        label: "Clientes activos",
        description: "Contactos disponibles para ventas, órdenes y facturación.",
    },
    {
        key: "ordenes_pendientes",
        label: "Órdenes pendientes",
        description: "Trabajos abiertos que requieren seguimiento.",
    },
    {
        key: "facturas_pendientes",
        fallbackKey: "registros_facturacion_pendientes",
        label: "Facturas pendientes",
        description: "Documentos o registros pendientes de revisar.",
    },
    {
        key: "productos_bajo_stock",
        label: "Productos bajo stock",
        description: "Artículos que necesitan reposición.",
    },
];

const resolveValue = (summary, item) => {
    const value = summary?.[item.key] ?? summary?.[item.fallbackKey];
    return Number.isFinite(Number(value)) ? Number(value) : 0;
};

export function BusinessStatusCards({ loading, summary }) {
    return (
        <section className="dashboard-section">
            <header className="dashboard-section-header">
                <h2>Estado de tu negocio</h2>
            </header>
            <div className="dashboard-business-status">
                {statusItems.map((item) => (
                    <article className="dashboard-status-card" key={item.key}>
                        <span>{item.label}</span>
                        <strong>{loading ? "..." : resolveValue(summary, item)}</strong>
                        <p>{item.description}</p>
                    </article>
                ))}
            </div>
        </section>
    );
}

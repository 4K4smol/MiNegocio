export function EmptyState({ title = "Sin resultados", description }) {
    return (
        <section className="admin-card admin-empty-inline">
            <strong>{title}</strong>
            {description ? <p>{description}</p> : null}
        </section>
    );
}

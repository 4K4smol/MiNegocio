export function EmptyState({
    children,
    className = "",
    description,
    title = "Sin resultados",
}) {
    return (
        <section
            className={["empty-state", className].filter(Boolean).join(" ")}
        >
            <strong>{title}</strong>
            {description || children ? <p>{description || children}</p> : null}
        </section>
    );
}

export function EmptyState({ title = "Sin resultados", children }) {
    return (
        <div className="empty-state">
            <strong>{title}</strong>
            {children ? <p>{children}</p> : null}
        </div>
    );
}

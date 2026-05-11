export function ErrorState({ title = "Ha ocurrido un error", children }) {
    return (
        <div className="error-state">
            <strong>{title}</strong>
            {children ? <p>{children}</p> : null}
        </div>
    );
}

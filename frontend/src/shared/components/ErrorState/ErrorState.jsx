export function ErrorState({ children = "No se ha podido cargar la información.", className = "", title }) {
    return (
        <div className={["error-state", className].filter(Boolean).join(" ")}>
            {title ? <strong>{title}</strong> : null}
            {children && title ? <p>{children}</p> : null}
            {children && !title ? children : null}
        </div>
    );
}

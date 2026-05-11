export function ErrorState({ children = "No se ha podido cargar la informacion." }) {
    return <div className="form-alert">{children}</div>;
}

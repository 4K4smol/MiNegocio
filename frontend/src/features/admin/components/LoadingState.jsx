export function LoadingState({ children = "Cargando..." }) {
    return <p className="admin-loading">{children}</p>;
}

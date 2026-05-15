export function LoadingState({ children = "Cargando...", className = "" }) {
    return <p className={["loading-state", className].filter(Boolean).join(" ")}>{children}</p>;
}

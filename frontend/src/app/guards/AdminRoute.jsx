import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../../shared/hooks/useAuth";

const isAdminUser = (usuario, session) =>
    usuario?.role === "admin" ||
    usuario?.rol === "admin" ||
    usuario?.tipo === "admin" ||
    session?.role === "admin" ||
    session?.rol === "admin";

export function AdminRoute() {
    const { usuario, session } = useAuth();

    if (!isAdminUser(usuario, session)) {
        return <Navigate to="/app" replace />;
    }

    return <Outlet />;
}

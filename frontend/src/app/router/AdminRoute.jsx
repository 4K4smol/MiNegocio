import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../../shared/hooks/useAuth";
import { hasRole } from "./roleUtils";

const isAdminUser = (usuario, session) =>
    hasRole(usuario, session, ["admin"]) ||
    session?.role?.nombre === "admin" ||
    usuario?.role?.nombre === "admin" ||
    usuario?.rol === "admin" ||
    session?.rol === "admin" ||
    usuario?.role === "admin" ||
    usuario?.tipo === "admin" ||
    session?.role === "admin" ||
    session?.rol?.nombre === "admin";

export function AdminRoute() {
    const { usuario, session } = useAuth();

    if (!isAdminUser(usuario, session)) {
        return <Navigate to="/app" replace />;
    }

    return <Outlet />;
}

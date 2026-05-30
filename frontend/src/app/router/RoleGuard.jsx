import { Navigate, Outlet } from "react-router-dom";
import { LoadingState } from "../../shared/components/LoadingState";
import { useAuth } from "../../shared/hooks/useAuth";
import { hasRole } from "./roleUtils";

export function RoleGuard({ allowedRoles = [], redirectTo = "/app" }) {
    const { usuario, session, isInitialized } = useAuth();

    if (!isInitialized) {
        return <LoadingState>Cargando sesion...</LoadingState>;
    }

    const canAccess = hasRole(usuario, session, allowedRoles);

    if (!canAccess) {
        return <Navigate to={redirectTo} replace />;
    }

    return <Outlet />;
}

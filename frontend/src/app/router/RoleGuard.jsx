import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../../shared/hooks/useAuth";
import { hasRole } from "./roleUtils";

export function RoleGuard({ allowedRoles = [], redirectTo = "/app" }) {
    const { usuario, session } = useAuth();
    const canAccess = hasRole(usuario, session, allowedRoles);

    if (!canAccess) {
        return <Navigate to={redirectTo} replace />;
    }

    return <Outlet />;
}

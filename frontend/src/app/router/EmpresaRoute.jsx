import { Navigate, Outlet } from "react-router-dom";
import { LoadingState } from "../../shared/components/LoadingState";
import { useAuth } from "../../shared/hooks/useAuth";
import { hasRole } from "./roleUtils";

const hasEmpresaAccess = (usuario, session) =>
    Boolean(
        usuario?.empresa_id ||
        usuario?.empresaId ||
        session?.empresa ||
        session?.empresa_id ||
        session?.empresaId,
    );

export function EmpresaRoute() {
    const { usuario, session, isInitialized, isLoading } = useAuth();

    if (!isInitialized || isLoading) {
        return <LoadingState>Cargando sesion...</LoadingState>;
    }

    if (hasRole(usuario, session, ["admin"])) {
        return <Navigate to="/admin" replace />;
    }

    if (!hasEmpresaAccess(usuario, session)) {
        return <Navigate to="/registro" replace />;
    }

    return <Outlet />;
}

import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../../shared/hooks/useAuth";

const hasEmpresaAccess = (usuario, session) =>
    Boolean(
        usuario?.empresa_id ||
        usuario?.empresaId ||
        session?.empresa ||
        session?.empresa_id ||
        session?.empresaId,
    );

export function EmpresaRoute() {
    const { usuario, session } = useAuth();

    if (!hasEmpresaAccess(usuario, session)) {
        return <Navigate to="/registro" replace />;
    }

    return <Outlet />;
}

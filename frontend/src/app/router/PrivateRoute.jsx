import { Navigate, Outlet, useLocation } from "react-router-dom";
import { LoadingState } from "../../shared/components/LoadingState";
import { useAuth } from "../../shared/hooks/useAuth";

export function PrivateRoute() {
    const { isAuthenticated, isInitialized, isLoading } = useAuth();
    const location = useLocation();

    if (!isInitialized || isLoading) {
        return <LoadingState>Cargando sesion...</LoadingState>;
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    return <Outlet />;
}

import { NavLink, Outlet } from "react-router-dom";
import { empresaNavigation } from "../app/config/navigation";
import { useAuth } from "../shared/hooks/useAuth";

export function EmpresaLayout() {
    const { logout, usuario } = useAuth();

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <div className="sidebar-header">
                    <span className="brand">MiNegocio</span>
                    <small>
                        {usuario?.name || usuario?.nombre || "Empresa"}
                    </small>
                </div>
                <nav className="sidebar-nav" aria-label="Menu de empresa">
                    {empresaNavigation.map((item) => (
                        <NavLink
                            key={item.to}
                            to={item.to}
                            end={item.to === "/app"}
                        >
                            {item.label}
                        </NavLink>
                    ))}
                </nav>
                <button className="text-button" type="button" onClick={logout}>
                    Cerrar sesion
                </button>
            </aside>
            <main className="app-main">
                <Outlet />
            </main>
        </div>
    );
}

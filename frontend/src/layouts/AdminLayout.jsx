import { NavLink, Outlet } from "react-router-dom";
import { adminNavigation } from "../app/config/navigation";
import { useAuth } from "../shared/hooks/useAuth";

export function AdminLayout() {
    const { logout, usuario } = useAuth();

    return (
        <div className="app-shell">
            <aside className="sidebar sidebar-admin">
                <div className="sidebar-header">
                    <span className="brand">MiNegocio Admin</span>
                    <small>
                        {usuario?.name || usuario?.nombre || "Administrador"}
                    </small>
                </div>
                <nav
                    className="sidebar-nav"
                    aria-label="Menu de administracion"
                >
                    {adminNavigation.map((item) => (
                        <NavLink
                            key={item.to}
                            to={item.to}
                            end={item.to === "/admin"}
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

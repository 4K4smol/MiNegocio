import { NavLink } from "react-router-dom";
import { adminNavigation } from "../../../app/config/navigation";
import { AppIcon } from "../../../components/ui/AppIcon";

export function AdminSidebar() {
    return (
        <aside className="admin-sidebar">
            <div className="admin-sidebar-brand">
                <img
                    src="/assets/brand/minegocio-logo-horizontal-transparente.png"
                    alt="MiNegocio"
                />
                <small>Panel administrador</small>
            </div>
            <nav className="admin-sidebar-nav" aria-label="Menú de administración">
                {adminNavigation.map((item) => (
                    <NavLink key={item.to} to={item.to} end>
                        <AppIcon icon={item.icon} size={18} />
                        {item.label}
                    </NavLink>
                ))}
            </nav>
        </aside>
    );
}

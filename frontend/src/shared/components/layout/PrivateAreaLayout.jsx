import { NavLink, Outlet, useLocation } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";

const getUserName = (usuario) =>
    usuario?.name || usuario?.nombre || usuario?.email || "Usuario";

const getSectionTitle = (pathname, navigation, fallback) => {
    const activeItem = navigation
        .filter((item) => pathname === item.to || pathname.startsWith(`${item.to}/`))
        .sort((a, b) => b.to.length - a.to.length)[0];

    return activeItem?.label || fallback;
};

export function PrivateAreaLayout({
    areaLabel,
    contentClassName = "app-main",
    defaultTitle = "Panel de control",
    navigation = [],
    onLogout,
    sidebarSubtitle,
    topbarEyebrow = "Panel de control",
    usuario,
    userRoleLabel,
    variant = "empresa",
}) {
    const location = useLocation();
    const userName = getUserName(usuario);

    return (
        <div className={`app-shell app-shell--${variant}`}>
            <aside className="sidebar">
                <div className="sidebar-header">
                    <img
                        className="sidebar-logo"
                        src="/assets/brand/minegocio-logo-horizontal-transparente.png"
                        alt="MiNegocio"
                    />
                    {sidebarSubtitle ? <small>{sidebarSubtitle}</small> : null}
                </div>
                <nav className="sidebar-nav" aria-label={areaLabel}>
                    {navigation.map((item) => (
                        <NavLink
                            key={item.to}
                            to={item.to}
                            end={item.to === "/app" || item.to === "/admin"}
                        >
                            <AppIcon icon={item.icon} size={18} />
                            {item.label}
                        </NavLink>
                    ))}
                </nav>
            </aside>

            <div className="app-content-shell">
                <header className="app-topbar">
                    <div>
                        <span className="eyebrow">{topbarEyebrow}</span>
                        <h1>{getSectionTitle(location.pathname, navigation, defaultTitle)}</h1>
                    </div>
                    <div className="app-topbar-user">
                        <div>
                            <span>{userName}</span>
                            <small>{userRoleLabel}</small>
                        </div>
                        <button className="button button-ghost" type="button" onClick={onLogout}>
                            Cerrar sesión
                        </button>
                    </div>
                </header>

                <main className={contentClassName}>
                    <Outlet />
                </main>
            </div>
        </div>
    );
}

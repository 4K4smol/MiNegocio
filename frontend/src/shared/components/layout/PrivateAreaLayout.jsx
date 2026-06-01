import { useState } from "react";
import { Menu, X } from "lucide-react";
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
    const [menuState, setMenuState] = useState({ open: false, pathname: location.pathname });
    const userName = getUserName(usuario);
    const title = getSectionTitle(location.pathname, navigation, defaultTitle);
    const menuOpen = menuState.open && menuState.pathname === location.pathname;
    const closeMenu = () => setMenuState({ open: false, pathname: location.pathname });

    const handleLogout = () => {
        closeMenu();
        onLogout?.();
    };

    return (
        <div className={`app-shell app-shell--${variant} ${menuOpen ? "is-menu-open" : ""}`}>
            <button
                aria-label="Cerrar menu"
                aria-hidden={!menuOpen}
                className="mobile-menu-backdrop"
                tabIndex={menuOpen ? 0 : -1}
                type="button"
                onClick={closeMenu}
            />
            <aside className="sidebar" id="private-area-menu">
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
                            onClick={closeMenu}
                        >
                            <AppIcon icon={item.icon} size={18} />
                            {item.label}
                        </NavLink>
                    ))}
                </nav>
            </aside>

            <div className="app-content-shell">
                <header className="app-topbar">
                    <div className="app-topbar-title">
                        <button
                            aria-controls="private-area-menu"
                            aria-expanded={menuOpen}
                            aria-label={menuOpen ? "Cerrar menu" : "Abrir menu"}
                            className="mobile-menu-toggle"
                            type="button"
                            onClick={() => setMenuState({ open: !menuOpen, pathname: location.pathname })}
                        >
                            {menuOpen ? <X aria-hidden="true" size={20} /> : <Menu aria-hidden="true" size={20} />}
                        </button>
                        <div>
                            <span className="eyebrow">{topbarEyebrow}</span>
                            <h1>{title}</h1>
                        </div>
                    </div>
                    <div className="app-topbar-user">
                        <div>
                            <span>{userName}</span>
                            <small>{userRoleLabel}</small>
                        </div>
                        <button className="button button-ghost" type="button" onClick={handleLogout}>
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

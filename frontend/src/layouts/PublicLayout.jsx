import { Link, Outlet, useLocation } from "react-router-dom";
import { useEffect } from "react";
import { AppIcon } from "../components/ui/AppIcon";
import { appIcons } from "../config/appIcons";

export function PublicLayout() {
    const { hash } = useLocation();

    useEffect(() => {
        if (!hash) return;
        const el = document.querySelector(hash);
        if (el) el.scrollIntoView({ behavior: "smooth" });
    }, [hash]);

    return (
        <div className="public-shell">
            <header className="public-header">
                <Link className="brand brand-link" to="/" aria-label="MiNegocio">
                    <img
                        className="brand-logo"
                        src="/assets/brand/minegocio-logo-horizontal-transparente.png"
                        alt="MiNegocio"
                    />
                </Link>
                <nav className="primary-nav" aria-label="Navegacion publica">
                    <Link to="/">Inicio</Link>
                    <Link to="/#modulos">M&oacute;dulos</Link>
                    <Link to="/#como-funciona">C&oacute;mo funciona</Link>
                </nav>
                <nav className="top-nav" aria-label="Acceso de usuarios">
                    <Link className="icon-link" to="/login">
                        <AppIcon icon={appIcons.login} size={18} />
                        Iniciar sesi&oacute;n
                    </Link>
                    <Link className="nav-button" to="/registro">
                        <AppIcon icon={appIcons.registro} size={18} />
                        Registro
                    </Link>
                </nav>
            </header>
            <main className="public-main">
                <Outlet />
            </main>
            <footer className="public-footer">
                <div>
                    <Link className="brand brand-link" to="/" aria-label="MiNegocio">
                        <img
                            className="brand-logo brand-logo-footer"
                            src="/assets/brand/minegocio-logo-horizontal-transparente.png"
                            alt="MiNegocio"
                        />
                    </Link>
                    <p>Plataforma integral para la gestion de tu negocio.</p>
                </div>
                <div className="footer-links" aria-label="Enlaces sociales">
                    <a href="https://www.linkedin.com" rel="noreferrer" target="_blank">
                        LinkedIn
                    </a>
                    <a href="https://x.com" rel="noreferrer" target="_blank">
                        X
                    </a>
                    <a href="mailto:info@minegocio.local">Contacto</a>
                </div>
                <small>&copy; 2026 MiNegocio. Todos los derechos reservados.</small>
            </footer>
        </div>
    );
}

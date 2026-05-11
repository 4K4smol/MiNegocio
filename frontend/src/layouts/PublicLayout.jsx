import { Link, Outlet } from "react-router-dom";

export function PublicLayout() {
    return (
        <div className="public-shell">
            <header className="public-header">
                <Link className="brand" to="/">
                    MiNegocio
                </Link>
                <nav className="top-nav" aria-label="Navegacion publica">
                    <Link to="/login">Acceder</Link>
                    <Link to="/registro">Registro</Link>
                </nav>
            </header>
            <main className="public-main">
                <Outlet />
            </main>
        </div>
    );
}

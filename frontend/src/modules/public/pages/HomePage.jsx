import { Link } from "react-router-dom";

export function HomePage() {
    return (
        <section className="page public-home">
            <header className="page-header">
                <h1>MiNegocio</h1>
                <p>
                    Gestion de clientes, servicios, ordenes, facturacion y
                    VeriFactu.
                </p>
            </header>
            <div className="actions">
                <Link className="button" to="/login">
                    Acceder
                </Link>
                <Link className="button button-secondary" to="/registro">
                    Registrar empresa
                </Link>
            </div>
        </section>
    );
}

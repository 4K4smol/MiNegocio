import { Link } from "react-router-dom";

export function FacturasPage() {
    return (
        <section className="page">
            <header className="page-header">
                <h1>Facturas</h1>
                <p>Listado preparado para facturasApi.list.</p>
            </header>
            <div className="actions">
                <Link to="/app/facturas/1">Ver factura de ejemplo</Link>
                <Link to="/app/facturas/registros">
                    Registros de facturacion
                </Link>
                <Link to="/app/facturas/verifactu">VeriFactu</Link>
            </div>
        </section>
    );
}

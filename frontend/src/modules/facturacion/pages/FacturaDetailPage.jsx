import { useParams } from "react-router-dom";

export function FacturaDetailPage() {
    const { facturaId } = useParams();

    return (
        <section className="page">
            <header className="page-header">
                <h1>Detalle de factura</h1>
                <p>
                    Detalle de la factura {facturaId} preparado para
                    facturasApi.get.
                </p>
            </header>
        </section>
    );
}

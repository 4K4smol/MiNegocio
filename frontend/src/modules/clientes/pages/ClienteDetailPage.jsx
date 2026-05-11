import { Link, useParams } from "react-router-dom";

export function ClienteDetailPage() {
    const { clienteId } = useParams();

    return (
        <section className="page">
            <header className="page-header page-header-row">
                <div>
                    <h1>Detalle de cliente</h1>
                    <p>
                        Detalle del cliente {clienteId} preparado para
                        clientesApi.get.
                    </p>
                </div>
                <Link
                    className="button"
                    to={`/app/clientes/${clienteId}/editar`}
                >
                    Editar
                </Link>
            </header>
        </section>
    );
}

import { Link } from "react-router-dom";
import { ClientesTable } from "../components/ClientesTable";

export function ClientesPage() {
    return (
        <section className="page">
            <header className="page-header page-header-row">
                <div>
                    <h1>Clientes</h1>
                    <p>
                        Listado base preparado para conectar con
                        clientesApi.list.
                    </p>
                </div>
                <Link className="button" to="/app/clientes/nuevo">
                    Nuevo cliente
                </Link>
            </header>
            <ClientesTable />
        </section>
    );
}

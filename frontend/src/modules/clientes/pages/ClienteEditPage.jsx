import { useParams } from "react-router-dom";
import { ClienteForm } from "../components/ClienteForm";

export function ClienteEditPage() {
    const { clienteId } = useParams();

    return (
        <section className="page">
            <header className="page-header">
                <h1>Editar cliente</h1>
                <p>
                    Edicion del cliente {clienteId} preparada para
                    clientesApi.update.
                </p>
            </header>
            <ClienteForm />
        </section>
    );
}

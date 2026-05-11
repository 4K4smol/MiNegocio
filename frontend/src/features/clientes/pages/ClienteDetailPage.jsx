import { Link, useParams } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";

export function ClienteDetailPage() {
    const { clienteId } = useParams();

    return (
        <section className="page">
            <header className="page-header page-header-row">
                <div>
                    <h1>Detalle de cliente</h1>
                    <p>
                        Detalle del cliente {clienteId} preparado para
                        clientesService.get.
                    </p>
                </div>
                <Link
                    className="button"
                    to={`/app/clientes/${clienteId}/editar`}
                >
                    <AppIcon icon={appIcons.editar} size={18} />
                    Editar
                </Link>
            </header>
        </section>
    );
}

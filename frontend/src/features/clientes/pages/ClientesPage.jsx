import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ClientesTable } from "../components/ClientesTable";

export function ClientesPage() {
    return (
        <section className="page">
            <header className="page-header page-header-row">
                <div>
                    <h1>Clientes</h1>
                    <p>
                        Listado base preparado para conectar con
                        clientesService.list.
                    </p>
                </div>
                <Link className="button" to="/app/clientes/nuevo">
                    <AppIcon icon={appIcons.crear} size={18} />
                    Nuevo cliente
                </Link>
            </header>
            <ClientesTable />
        </section>
    );
}

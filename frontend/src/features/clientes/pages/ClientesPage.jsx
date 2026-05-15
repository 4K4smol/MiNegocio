import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { PageHeader } from "../../../shared/components/PageHeader";
import { ClientesTable } from "../components/ClientesTable";

export function ClientesPage() {
    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to="/app/clientes/nuevo">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nuevo cliente
                    </Link>
                }
                description="Listado base preparado para conectar con clientesService.list."
                title="Clientes"
            />
            <ClientesTable />
        </section>
    );
}

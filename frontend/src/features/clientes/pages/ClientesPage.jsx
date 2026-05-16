import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { ClientesTable } from "../components/ClientesTable";
import { clientesService } from "../services/clientesService";

export function ClientesPage() {
    const [clientes, setClientes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        let mounted = true;

        clientesService.list({ per_page: 100 })
            .then((response) => {
                if (mounted) setClientes(unwrapApiCollection(response));
            })
            .catch((currentError) => {
                if (mounted) setError(currentError?.message || "No se han podido cargar los clientes.");
            })
            .finally(() => {
                if (mounted) setLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to="/app/clientes/nuevo">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nuevo cliente
                    </Link>
                }
                description="Consulta y gestiona los clientes de tu empresa."
                title="Clientes"
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? <LoadingState>Cargando clientes...</LoadingState> : <ClientesTable clientes={clientes} />}
        </section>
    );
}

import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { ClienteForm } from "../components/ClienteForm";
import { clientesService } from "../services/clientesService";

const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

export function ClienteCreatePage() {
    const navigate = useNavigate();
    const [tiposCliente, setTiposCliente] = useState([]);
    const [loadingTipos, setLoadingTipos] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState("");
    const [errors, setErrors] = useState({});

    useEffect(() => {
        let mounted = true;

        clientesService.getTiposCliente({ per_page: 100 })
            .then((response) => {
                if (mounted) setTiposCliente(unwrapApiCollection(response));
            })
            .catch((currentError) => {
                if (mounted) setError(currentError?.message || "No se han podido cargar los tipos de cliente.");
            })
            .finally(() => {
                if (mounted) setLoadingTipos(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    const handleCreateCliente = async (payload) => {
        setIsSubmitting(true);
        setError("");
        setErrors({});

        try {
            const response = await clientesService.create(payload);
            const cliente = unwrapApiData(response);
            navigate(cliente?.id ? `/app/clientes/${cliente.id}` : "/app/clientes");
        } catch (currentError) {
            setErrors(validationErrors(currentError));
            setError(currentError?.message || "No se ha podido crear el cliente.");
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                description="Registra un cliente asociado a tu empresa."
                title="Nuevo cliente"
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            {loadingTipos ? (
                <LoadingState>Cargando tipos de cliente...</LoadingState>
            ) : (
                <ClienteForm
                    errors={errors}
                    isSubmitting={isSubmitting}
                    tiposCliente={tiposCliente}
                    onSubmit={handleCreateCliente}
                />
            )}
        </section>
    );
}

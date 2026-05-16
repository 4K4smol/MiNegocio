import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { ClienteForm } from "../components/ClienteForm";
import { clientesService } from "../services/clientesService";

const validationErrors = (error) => error?.errors || error?.payload?.errors || {};

export function ClienteEditPage() {
    const { clienteId } = useParams();
    const navigate = useNavigate();
    const [cliente, setCliente] = useState(null);
    const [tiposCliente, setTiposCliente] = useState([]);
    const [loading, setLoading] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState("");
    const [errors, setErrors] = useState({});

    useEffect(() => {
        let mounted = true;

        Promise.all([
            clientesService.get(clienteId),
            clientesService.getTiposCliente({ per_page: 100 }),
        ])
            .then(([clienteResponse, tiposResponse]) => {
                if (!mounted) return;
                setCliente(unwrapApiData(clienteResponse));
                setTiposCliente(unwrapApiCollection(tiposResponse));
            })
            .catch((currentError) => {
                if (mounted) setError(currentError?.message || "No se ha podido cargar el cliente.");
            })
            .finally(() => {
                if (mounted) setLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, [clienteId]);

    const handleUpdateCliente = async (payload) => {
        setIsSubmitting(true);
        setError("");
        setErrors({});

        try {
            await clientesService.update(clienteId, payload);
            navigate(`/app/clientes/${clienteId}`);
        } catch (currentError) {
            setErrors(validationErrors(currentError));
            setError(currentError?.message || "No se ha podido actualizar el cliente.");
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                description="Actualiza los datos comerciales y de contacto del cliente."
                title="Editar cliente"
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando cliente...</LoadingState>
            ) : (
                <ClienteForm
                    errors={errors}
                    initialValues={cliente || {}}
                    isSubmitting={isSubmitting}
                    submitLabel="Actualizar cliente"
                    tiposCliente={tiposCliente}
                    onSubmit={handleUpdateCliente}
                />
            )}
        </section>
    );
}

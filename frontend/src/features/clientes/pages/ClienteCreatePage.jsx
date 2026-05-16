import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { FormModal } from "../../../shared/components/FormModal";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { ClienteForm } from "../components/ClienteForm";
import { clientesService } from "../services/clientesService";
import { clientePayloadFromForm, validationErrors } from "../utils/clienteForm";

export function ClienteCreatePage() {
    const navigate = useNavigate();
    const [tiposCliente, setTiposCliente] = useState([]);
    const [loadingTipos, setLoadingTipos] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [tiposError, setTiposError] = useState("");
    const [formError, setFormError] = useState("");
    const [errors, setErrors] = useState({});

    useEffect(() => {
        let mounted = true;

        clientesService.getTiposCliente({ per_page: 100 })
            .then((response) => {
                if (mounted) setTiposCliente(unwrapApiCollection(response));
            })
            .catch((currentError) => {
                if (mounted) setTiposError(currentError?.message || "No se han podido cargar los tipos de cliente.");
            })
            .finally(() => {
                if (mounted) setLoadingTipos(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    const closePage = () => navigate("/app/clientes");

    const handleCreateCliente = async (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        setFormError("");
        setErrors({});

        try {
            const response = await clientesService.create(clientePayloadFromForm(event.currentTarget));
            const cliente = unwrapApiData(response);
            navigate(cliente?.id ? `/app/clientes/${cliente.id}` : "/app/clientes");
        } catch (currentError) {
            setErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido crear el cliente.");
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
            <FormModal
                error={formError || tiposError}
                loading={isSubmitting}
                mode="create"
                open
                submitDisabled={loadingTipos || Boolean(tiposError)}
                submitLabel="Crear cliente"
                title="Nuevo cliente"
                onClose={closePage}
                onSubmit={handleCreateCliente}
            >
                {loadingTipos ? (
                    <LoadingState>Cargando tipos de cliente...</LoadingState>
                ) : (
                    <ClienteForm disabled={isSubmitting || Boolean(tiposError)} errors={errors} tiposCliente={tiposCliente} />
                )}
            </FormModal>
        </section>
    );
}

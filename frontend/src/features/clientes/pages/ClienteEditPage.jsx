import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { FormModal } from "../../../shared/components/FormModal";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection, unwrapApiData } from "../../../shared/utils/apiResponse";
import { ClienteForm } from "../components/ClienteForm";
import { clientesService } from "../services/clientesService";
import { clientePayloadFromForm, validationErrors } from "../utils/clienteForm";

export function ClienteEditPage() {
    const { clienteId } = useParams();
    const navigate = useNavigate();
    const [cliente, setCliente] = useState(null);
    const [tiposCliente, setTiposCliente] = useState([]);
    const [loading, setLoading] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [loadError, setLoadError] = useState("");
    const [formError, setFormError] = useState("");
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
                if (mounted) setLoadError(currentError?.message || "No se ha podido cargar el cliente.");
            })
            .finally(() => {
                if (mounted) setLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, [clienteId]);

    const closePage = () => navigate(`/app/clientes/${clienteId}`);

    const handleUpdateCliente = async (event) => {
        event.preventDefault();
        setIsSubmitting(true);
        setFormError("");
        setErrors({});

        try {
            await clientesService.update(clienteId, clientePayloadFromForm(event.currentTarget));
            navigate(`/app/clientes/${clienteId}`);
        } catch (currentError) {
            setErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido actualizar el cliente.");
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
            <FormModal
                error={formError || loadError}
                loading={isSubmitting}
                mode="edit"
                open
                submitDisabled={loading || Boolean(loadError)}
                submitLabel="Guardar cambios"
                title="Editar cliente"
                onClose={closePage}
                onSubmit={handleUpdateCliente}
            >
                {loading ? (
                    <LoadingState>Cargando cliente...</LoadingState>
                ) : (
                    <ClienteForm
                        disabled={isSubmitting || Boolean(loadError)}
                        errors={errors}
                        initialValues={cliente || {}}
                        tiposCliente={tiposCliente}
                    />
                )}
            </FormModal>
        </section>
    );
}

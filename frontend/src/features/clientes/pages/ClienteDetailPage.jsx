import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { clientesService } from "../services/clientesService";

const getDisplayName = (cliente) =>
    cliente.razon_social || [cliente.nombre, cliente.apellidos].filter(Boolean).join(" ");

function DetailItem({ label, value }) {
    return (
        <div>
            <dt>{label}</dt>
            <dd>{value || "No indicado"}</dd>
        </div>
    );
}

export function ClienteDetailPage() {
    const { clienteId } = useParams();
    const [cliente, setCliente] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        let mounted = true;

        clientesService.get(clienteId)
            .then((response) => {
                if (mounted) setCliente(unwrapApiData(response));
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

    if (loading) return <LoadingState>Cargando cliente...</LoadingState>;
    if (error) return <ErrorState>{error}</ErrorState>;
    if (!cliente) return <ErrorState>No se ha encontrado el cliente.</ErrorState>;

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to={`/app/clientes/${clienteId}/editar`}>
                        <AppIcon icon={appIcons.editar} size={18} />
                        Editar
                    </Link>
                }
                description={cliente.dni_cif}
                title={getDisplayName(cliente)}
            />

            <section className="card">
                <dl className="detail-list">
                    <DetailItem label="Tipo" value={cliente.tipo_cliente?.nombre} />
                    <DetailItem label="Nombre" value={cliente.nombre} />
                    <DetailItem label="Apellidos" value={cliente.apellidos} />
                    <DetailItem label="Razón social" value={cliente.razon_social} />
                    <DetailItem label="DNI/CIF" value={cliente.dni_cif} />
                    <DetailItem label="Teléfono" value={cliente.telefono} />
                    <DetailItem label="Email" value={cliente.email} />
                    <DetailItem label="Persona de contacto" value={cliente.persona_contacto} />
                    <DetailItem label="Estado" value={cliente.activo ? "Activo" : "Inactivo"} />
                    <DetailItem label="Notas" value={cliente.notas} />
                    <DetailItem label="Dirección" value={cliente.direccion} />
                    <DetailItem label="Ciudad" value={cliente.ciudad} />
                    <DetailItem label="Provincia" value={cliente.provincia} />
                    <DetailItem label="Código postal" value={cliente.codigo_postal} />
                    <DetailItem label="País" value={cliente.pais} />
                </dl>
            </section>
        </section>
    );
}

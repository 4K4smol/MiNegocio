import { PageHeader } from "../../../shared/components/PageHeader";
import { ServiciosTab } from "../components/ServiciosTab";

export function ServiciosPage({ initialAction = null, initialServicioId = null }) {
    return (
        <section className="page">
            <PageHeader
                description="Crea los servicios que ofrece tu empresa y define precios propios usando los tipos de tarifa globales."
                title="Servicios"
            />

            <ServiciosTab initialAction={initialAction} initialServicioId={initialServicioId} />
        </section>
    );
}

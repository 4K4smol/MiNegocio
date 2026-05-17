import { PageHeader } from "../../../shared/components/PageHeader";
import { ServiciosTab } from "../components/ServiciosTab";

export function ServiciosPage() {
    return (
        <section className="page">
            <PageHeader
                description="Crea los servicios que ofrece tu empresa y define precios propios usando los tipos de tarifa globales."
                title="Servicios"
            />

            <ServiciosTab />
        </section>
    );
}

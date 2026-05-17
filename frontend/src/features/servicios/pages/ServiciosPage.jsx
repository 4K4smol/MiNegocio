import { useState } from "react";
import { PageHeader } from "../../../shared/components/PageHeader";
import { CategoriasServiciosTab } from "../components/CategoriasServiciosTab";
import { ServicioTarifasTab } from "../components/ServicioTarifasTab";
import { ServiciosTab } from "../components/ServiciosTab";
import { ServiciosTabs } from "../components/ServiciosTabs";

export function ServiciosPage() {
    const [activeTab, setActiveTab] = useState("servicios");

    return (
        <section className="page">
            <PageHeader
                description="Crea los servicios que ofrece tu empresa y define sus precios. Las tarifas (Estandar, Urgente, Especial, Fin de semana) ya estan creadas automaticamente."
                title="Servicios"
            />

            <ServiciosTabs activeTab={activeTab} onChange={setActiveTab} />

            {activeTab === "servicios" ? <ServiciosTab /> : null}
            {activeTab === "categorias" ? <CategoriasServiciosTab /> : null}
            {activeTab === "tarifas" ? <ServicioTarifasTab /> : null}
        </section>
    );
}

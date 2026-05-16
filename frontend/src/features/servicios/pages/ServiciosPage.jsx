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
                description="Gestiona el catalogo de servicios, categorias logicas, tarifas y precios de tu empresa."
                title="Servicios"
            />

            <ServiciosTabs activeTab={activeTab} onChange={setActiveTab} />

            {activeTab === "servicios" ? <ServiciosTab /> : null}
            {activeTab === "categorias" ? <CategoriasServiciosTab /> : null}
            {activeTab === "tarifas" ? <ServicioTarifasTab /> : null}
        </section>
    );
}

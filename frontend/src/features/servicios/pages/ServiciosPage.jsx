import { useState } from "react";
import { PageHeader } from "../../../shared/components/PageHeader";
import { CategoriasServiciosTab } from "../components/CategoriasServiciosTab";
import { ServiciosTab } from "../components/ServiciosTab";
import { ServiciosTabs } from "../components/ServiciosTabs";
import { TiposTarifaServicioTab } from "../components/TiposTarifaServicioTab";

export function ServiciosPage() {
    const [activeTab, setActiveTab] = useState("servicios");

    return (
        <section className="page">
            <PageHeader
                description="Crea los servicios que ofrece tu empresa y define precios propios usando los tipos de tarifa globales."
                title="Servicios"
            />

            <ServiciosTabs activeTab={activeTab} onChange={setActiveTab} />

            {activeTab === "servicios" ? <ServiciosTab /> : null}
            {activeTab === "categorias" ? <CategoriasServiciosTab /> : null}
            {activeTab === "tarifas" ? <TiposTarifaServicioTab /> : null}
        </section>
    );
}

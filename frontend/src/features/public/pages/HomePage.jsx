import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { DashboardPreview } from "../components/DashboardPreview";
import { ModuleCard } from "../components/ModuleCard";

const modules = [
    {
        icon: appIcons.clientes,
        title: "Clientes",
        description: "Centraliza datos fiscales, localizaciones, historial y contacto.",
    },
    {
        icon: appIcons.ordenes,
        title: "Ordenes de trabajo",
        description: "Planifica, asigna y controla servicios pendientes o completados.",
    },
    {
        icon: appIcons.facturas,
        title: "Facturacion",
        description:
            "Genera facturas desde ordenes completadas y prepara el sistema para trazabilidad legal.",
    },
    {
        icon: appIcons.inventario,
        title: "Inventario",
        description: "Controla productos, movimientos, ubicaciones y stock.",
    },
    {
        icon: appIcons.calendario,
        title: "Calendario",
        description: "Visualiza servicios programados y tareas pendientes.",
    },
    {
        icon: appIcons.informes,
        title: "Informes",
        description: "Consulta metricas mensuales y evolucion del negocio.",
    },
];

export function HomePage() {
    return (
        <div className="public-home">
            <section className="hero-section">
                <div className="hero-copy">
                    <span className="eyebrow">CRM para autonomos y PYMES</span>
                    <h1>Gestiona tu negocio desde una sola plataforma</h1>
                    <p>
                        Clientes, ordenes de trabajo, facturas, inventario,
                        calendario e informes en un unico sistema pensado para
                        autonomos y pequenas empresas.
                    </p>
                    <div className="actions">
                        <Link className="button" to="/registro">
                            <AppIcon icon={appIcons.registro} size={18} />
                            Comenzar registro
                        </Link>
                        <Link className="button button-secondary" to="/login">
                            <AppIcon icon={appIcons.login} size={18} />
                            Iniciar sesion
                        </Link>
                    </div>
                </div>
                <DashboardPreview />
            </section>

            <section
                className="modules-section"
                id="modulos"
                aria-labelledby="modules-title"
            >
                <div className="section-heading">
                    <span className="eyebrow">Modulos</span>
                    <h2 id="modules-title">Todo conectado para trabajar mejor</h2>
                    <p>
                        Una base operativa sobria y escalable para controlar el
                        dia a dia sin duplicar informacion.
                    </p>
                </div>
                <div className="modules-grid">
                    {modules.map((module) => (
                        <ModuleCard key={module.title} {...module} />
                    ))}
                </div>
            </section>

            <section
                className="workflow-section"
                id="como-funciona"
                aria-labelledby="workflow-title"
            >
                <div className="section-heading">
                    <span className="eyebrow">Como funciona</span>
                    <h2 id="workflow-title">De la operacion diaria a la decision</h2>
                    <p>
                        MiNegocio organiza el flujo de trabajo desde el primer
                        contacto hasta la factura, manteniendo la informacion
                        conectada y lista para consultar.
                    </p>
                </div>
                <div className="workflow-grid">
                    <article>
                        <span>01</span>
                        <h3>Centraliza</h3>
                        <p>Reune clientes, servicios y datos operativos en un mismo lugar.</p>
                    </article>
                    <article>
                        <span>02</span>
                        <h3>Ejecuta</h3>
                        <p>Planifica ordenes, controla inventario y convierte trabajo en facturas.</p>
                    </article>
                    <article>
                        <span>03</span>
                        <h3>Analiza</h3>
                        <p>Consulta el calendario, el estado de ventas y los informes clave.</p>
                    </article>
                </div>
            </section>
        </div>
    );
}

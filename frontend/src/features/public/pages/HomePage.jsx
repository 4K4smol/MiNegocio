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
        title: "Órdenes de trabajo",
        description: "Planifica, asigna y controla servicios pendientes o completados.",
    },
    {
        icon: appIcons.facturas,
        title: "Facturación",
        description:
            "Genera facturas desde órdenes completadas y prepara el sistema para trazabilidad legal.",
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
];

export function HomePage() {
    return (
        <div className="public-home">
            <section className="hero-section">
                <div className="hero-copy">
                    <span className="eyebrow">CRM para autónomos y PYMES</span>
                    <h1>Gestiona tu negocio desde una sola plataforma</h1>
                    <p>
                        Clientes, órdenes de trabajo, facturas, inventario y
                        calendario en un único sistema pensado para
                        autónomos y pequeñas empresas.
                    </p>
                    <div className="actions">
                        <Link className="button" to="/registro">
                            <AppIcon icon={appIcons.registro} size={18} />
                            Comenzar registro
                        </Link>
                        <Link className="button button-secondary" to="/login">
                            <AppIcon icon={appIcons.login} size={18} />
                            Iniciar sesión
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
                    <span className="eyebrow">Módulos</span>
                    <h2 id="modules-title">Todo conectado para trabajar mejor</h2>
                    <p>
                        Una base operativa sobria y escalable para controlar el
                        día a día sin duplicar información.
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
                    <span className="eyebrow">Cómo funciona</span>
                    <h2 id="workflow-title">De la operación diaria a la decisión</h2>
                    <p>
                        MiNegocio organiza el flujo de trabajo desde el primer
                        contacto hasta la factura, manteniendo la información
                        conectada y lista para consultar.
                    </p>
                </div>
                <div className="workflow-grid">
                    <article>
                        <span>01</span>
                        <h3>Centraliza</h3>
                        <p>Reúne clientes, servicios y datos operativos en un mismo lugar.</p>
                    </article>
                    <article>
                        <span>02</span>
                        <h3>Ejecuta</h3>
                        <p>Planifica órdenes, controla inventario y convierte trabajo en facturas.</p>
                    </article>
                    <article>
                        <span>03</span>
                        <h3>Analiza</h3>
                        <p>Consulta el calendario y el estado de ventas desde el mismo flujo.</p>
                    </article>
                </div>
            </section>
        </div>
    );
}

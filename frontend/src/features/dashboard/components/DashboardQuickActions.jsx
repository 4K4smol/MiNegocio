import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";

const quickActions = [
    {
        title: "Clientes",
        description: "Gestiona tus clientes y sus datos.",
        to: "/app/clientes",
        icon: appIcons.clientes,
    },
    {
        title: "Servicios",
        description: "Configura los servicios de tu empresa.",
        to: "/app/servicios",
        icon: appIcons.servicios,
    },
    {
        title: "Inventario",
        description: "Controla productos, materiales y stock.",
        to: "/app/inventario",
        icon: appIcons.inventario,
    },
    {
        title: "Órdenes",
        description: "Planifica y revisa los trabajos.",
        to: "/app/ordenes-trabajo",
        icon: appIcons.ordenes,
    },
    {
        title: "Facturación",
        description: "Consulta y gestiona tus facturas.",
        to: "/app/facturas",
        icon: appIcons.facturas,
    },
];

export function DashboardQuickActions() {
    return (
        <section className="dashboard-section">
            <header className="dashboard-section-header">
                <h2>Accesos rápidos</h2>
            </header>
            <div className="dashboard-quick-actions">
                {quickActions.map((action) => (
                    <Link className="dashboard-quick-card" key={action.to} to={action.to}>
                        <span className="dashboard-quick-card__icon">
                            <AppIcon icon={action.icon} size={22} />
                        </span>
                        <strong>{action.title}</strong>
                        <p>{action.description}</p>
                    </Link>
                ))}
            </div>
        </section>
    );
}

import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";

const quickActions = [
    {
        title: "Crear cliente",
        description: "Registra un nuevo cliente y deja sus datos listos para operar.",
        to: "/app/clientes?crear=1",
        icon: appIcons.clientes,
    },
    {
        title: "Crear orden de trabajo",
        description: "Programa una intervención, asigna datos y prepara el seguimiento.",
        to: "/app/ordenes-trabajo/nueva",
        icon: appIcons.ordenes,
    },
    {
        title: "Crear factura",
        description: "Accede a facturación para emitir desde una orden completada.",
        // Cambiar a la ruta de creación directa cuando exista en React Router.
        to: "/app/facturas",
        icon: appIcons.facturas,
    },
    {
        title: "Añadir servicio",
        description: "Da de alta un servicio para reutilizarlo en órdenes y facturas.",
        to: "/app/servicios/nuevo",
        icon: appIcons.servicios,
    },
    {
        title: "Añadir producto",
        description: "Incorpora un artículo o material al control de inventario.",
        to: "/app/inventario/nuevo",
        icon: appIcons.inventario,
    },
];

export function DashboardQuickActions() {
    return (
        <section className="dashboard-section">
            <header className="dashboard-section-header">
                <h2>Acciones rápidas</h2>
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

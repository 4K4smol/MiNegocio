import { appIcons } from "../../config/appIcons";

export const empresaNavigation = [
    { label: "Dashboard", to: "/app", icon: appIcons.dashboard },
    { label: "Clientes", to: "/app/clientes", icon: appIcons.clientes },
    { label: "Servicios", to: "/app/servicios", icon: appIcons.servicios },
    { label: "Inventario", to: "/app/inventario", icon: appIcons.inventario },
    { label: "Órdenes", to: "/app/ordenes-trabajo", icon: appIcons.ordenes },
    { label: "Facturación", to: "/app/facturas", icon: appIcons.facturas },
];

export const adminNavigation = [
    { label: "Dashboard", to: "/admin", icon: appIcons.dashboard },
    { label: "Solicitudes", to: "/admin/solicitudes", icon: appIcons.validar },
    { label: "Usuarios", to: "/admin/usuarios", icon: appIcons.usuarios },
    { label: "Empresas", to: "/admin/empresas", icon: appIcons.empresa },
    { label: "Auditoría", to: "/admin/auditoria", icon: appIcons.auditoria },
    {
        label: "Configuración",
        to: "/admin/configuracion",
        icon: appIcons.configuracion,
    },
];

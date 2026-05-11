export const clientesModuleRoutes = {
    index: "/app/clientes",
    create: "/app/clientes/nuevo",
    detail: (clienteId = ":clienteId") => `/app/clientes/${clienteId}`,
    edit: (clienteId = ":clienteId") => `/app/clientes/${clienteId}/editar`,
};

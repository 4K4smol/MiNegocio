export const facturacionModuleRoutes = {
    facturas: "/app/facturas",
    factura: (facturaId = ":facturaId") => `/app/facturas/${facturaId}`,
    crearFactura: "/app/facturas/nueva",
    registros: "/app/facturas/registros",
    verifactu: "/app/facturas/verifactu",
};

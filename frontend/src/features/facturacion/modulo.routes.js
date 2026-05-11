export const facturacionModuleRoutes = {
    facturas: "/app/facturas",
    factura: (facturaId = ":facturaId") => `/app/facturas/${facturaId}`,
    registros: "/app/facturas/registros",
    verifactu: "/app/facturas/verifactu",
};

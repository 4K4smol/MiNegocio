import { Route } from "react-router-dom";
import { PrivateLayout } from "../../layouts/PrivateLayout";
import { CalendarioPage } from "../../features/calendario/pages/CalendarioPage";
import { ClienteCreatePage } from "../../features/clientes/pages/ClienteCreatePage";
import { ClienteDetailPage } from "../../features/clientes/pages/ClienteDetailPage";
import { ClienteEditPage } from "../../features/clientes/pages/ClienteEditPage";
import { ClientesPage } from "../../features/clientes/pages/ClientesPage";
import { ConfiguracionPage } from "../../features/empresa/pages/ConfiguracionPage";
import { DashboardPage } from "../../features/dashboard/pages/DashboardPage";
import { FacturaDetailPage } from "../../features/facturacion/pages/FacturaDetailPage";
import { FacturasPage } from "../../features/facturacion/pages/FacturasPage";
import { RegistrosFacturacionPage } from "../../features/facturacion/pages/RegistrosFacturacionPage";
import { VerifactuPage } from "../../features/facturacion/pages/VerifactuPage";
import { InformesPage } from "../../features/informes/pages/InformesPage";
import { InventarioPage } from "../../features/inventario/pages/InventarioPage";
import { OrdenesTrabajoPage } from "../../features/ordenesTrabajo/pages/OrdenesTrabajoPage";
import { ServiciosPage } from "../../features/servicios/pages/ServiciosPage";
import { EmpresaRoute } from "./EmpresaRoute";
import { PrivateRoute } from "./PrivateRoute";

export const privateRoutes = (
    <Route element={<PrivateRoute />}>
        <Route element={<EmpresaRoute />}>
            <Route path="app" element={<PrivateLayout />}>
                <Route index element={<DashboardPage />} />
                <Route path="clientes" element={<ClientesPage />} />
                <Route path="clientes/nuevo" element={<ClienteCreatePage />} />
                <Route path="clientes/:clienteId" element={<ClienteDetailPage />} />
                <Route path="clientes/:clienteId/editar" element={<ClienteEditPage />} />
                <Route path="servicios" element={<ServiciosPage />} />
                <Route path="ordenes-trabajo" element={<OrdenesTrabajoPage />} />
                <Route path="calendario" element={<CalendarioPage />} />
                <Route path="facturas" element={<FacturasPage />} />
                <Route path="facturas/:facturaId" element={<FacturaDetailPage />} />
                <Route path="facturas/registros" element={<RegistrosFacturacionPage />} />
                <Route path="facturas/verifactu" element={<VerifactuPage />} />
                <Route path="inventario" element={<InventarioPage />} />
                <Route path="informes" element={<InformesPage />} />
                <Route path="configuracion" element={<ConfiguracionPage />} />
            </Route>
        </Route>
    </Route>
);

import { Navigate, Route } from "react-router-dom";
import { PrivateLayout } from "../../layouts/PrivateLayout";
import { CalendarioPage } from "../../features/calendario/pages/CalendarioPage";
import { ClienteCreatePage } from "../../features/clientes/pages/ClienteCreatePage";
import { ClienteDetailPage } from "../../features/clientes/pages/ClienteDetailPage";
import { ClienteEditPage } from "../../features/clientes/pages/ClienteEditPage";
import { ClientesPage } from "../../features/clientes/pages/ClientesPage";
import { ConfiguracionPage } from "../../features/empresa/pages/ConfiguracionPage";
import { EmpresaUbicacionesPage } from "../../features/empresa/pages/EmpresaUbicacionesPage";
import { DashboardPage } from "../../features/dashboard/pages/DashboardPage";
import { CrearFacturaPage } from "../../features/facturacion/pages/CrearFacturaPage";
import { FacturaDetailPage } from "../../features/facturacion/pages/FacturaDetailPage";
import { FacturasPage } from "../../features/facturacion/pages/FacturasPage";
import { RegistrosFacturacionPage } from "../../features/facturacion/pages/RegistrosFacturacionPage";
import { VerifactuPage } from "../../features/facturacion/pages/VerifactuPage";
import { InventarioCreatePage } from "../../features/inventario/pages/InventarioCreatePage";
import { InventarioEditPage } from "../../features/inventario/pages/InventarioEditPage";
import { OrdenCreatePage } from "../../features/ordenes/pages/OrdenCreatePage";
import { OrdenDetailPage } from "../../features/ordenes/pages/OrdenDetailPage";
import { OrdenEditPage } from "../../features/ordenes/pages/OrdenEditPage";
import { OrdenesPage } from "../../features/ordenes/pages/OrdenesPage";
import { ServicioCreatePage } from "../../features/servicios/pages/ServicioCreatePage";
import { ServicioEditPage } from "../../features/servicios/pages/ServicioEditPage";
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
                <Route path="servicios/nuevo" element={<ServicioCreatePage />} />
                <Route path="servicios/:servicioId/editar" element={<ServicioEditPage />} />
                <Route path="ordenes" element={<Navigate to="/app/ordenes-trabajo" replace />} />
                <Route path="ordenes-trabajo" element={<OrdenesPage />} />
                <Route path="ordenes-trabajo/nueva" element={<OrdenCreatePage />} />
                <Route path="ordenes-trabajo/:ordenId" element={<OrdenDetailPage />} />
                <Route path="ordenes-trabajo/:ordenId/editar" element={<OrdenEditPage />} />
                <Route path="calendario" element={<CalendarioPage />} />
                <Route path="facturacion" element={<Navigate to="/app/facturas" replace />} />
                <Route path="facturas" element={<FacturasPage />} />
                <Route path="facturas/nueva" element={<CrearFacturaPage />} />
                <Route path="facturas/registros" element={<RegistrosFacturacionPage />} />
                <Route path="facturas/verifactu" element={<VerifactuPage />} />
                <Route path="facturas/:facturaId" element={<FacturaDetailPage />} />
                <Route path="ubicaciones" element={<EmpresaUbicacionesPage />} />
                <Route path="inventario" element={<Navigate to="/app/ubicaciones" replace />} />
                <Route path="inventario/nuevo" element={<InventarioCreatePage />} />
                <Route path="inventario/:itemId/editar" element={<InventarioEditPage />} />
                <Route path="configuracion" element={<ConfiguracionPage />} />
            </Route>
        </Route>
    </Route>
);

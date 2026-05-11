import { BrowserRouter, Navigate, Route, Routes } from "react-router-dom";
import { AdminRoute } from "../app/guards/AdminRoute";
import { EmpresaRoute } from "../app/guards/EmpresaRoute";
import { PrivateRoute } from "../app/guards/PrivateRoute";
import { AdminLayout } from "../layouts/AdminLayout";
import { EmpresaLayout } from "../layouts/EmpresaLayout";
import { PublicLayout } from "../layouts/PublicLayout";
import { AdminConfiguracionPage } from "../modules/admin/pages/AdminConfiguracionPage";
import { AdminDashboardPage } from "../modules/admin/pages/AdminDashboardPage";
import { AdminSolicitudesPage } from "../modules/admin/pages/AdminSolicitudesPage";
import { LoginPage } from "../modules/auth/pages/LoginPage";
import { CalendarioPage } from "../modules/calendario/pages/CalendarioPage";
import { ClienteCreatePage } from "../modules/clientes/pages/ClienteCreatePage";
import { ClienteDetailPage } from "../modules/clientes/pages/ClienteDetailPage";
import { ClienteEditPage } from "../modules/clientes/pages/ClienteEditPage";
import { ClientesPage } from "../modules/clientes/pages/ClientesPage";
import { ConfiguracionPage } from "../modules/configuracion/pages/ConfiguracionPage";
import { DashboardPage } from "../modules/dashboard/pages/DashboardPage";
import { FacturaDetailPage } from "../modules/facturacion/pages/FacturaDetailPage";
import { FacturasPage } from "../modules/facturacion/pages/FacturasPage";
import { RegistrosFacturacionPage } from "../modules/facturacion/pages/RegistrosFacturacionPage";
import { VerifactuPage } from "../modules/facturacion/pages/VerifactuPage";
import { InformesPage } from "../modules/informes/pages/InformesPage";
import { InventarioPage } from "../modules/inventario/pages/InventarioPage";
import { OrdenesTrabajoPage } from "../modules/ordenes-trabajo/pages/OrdenesTrabajoPage";
import { HomePage } from "../modules/public/pages/HomePage";
import { RegistroPage } from "../modules/registro/pages/RegistroPage";
import { ServiciosPage } from "../modules/servicios/pages/ServiciosPage";

export function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                <Route element={<PublicLayout />}>
                    <Route index element={<HomePage />} />
                    <Route path="login" element={<LoginPage />} />
                    <Route path="registro" element={<RegistroPage />} />
                </Route>

                <Route element={<PrivateRoute />}>
                    <Route element={<EmpresaRoute />}>
                        <Route path="app" element={<EmpresaLayout />}>
                            <Route index element={<DashboardPage />} />
                            <Route path="clientes" element={<ClientesPage />} />
                            <Route
                                path="clientes/nuevo"
                                element={<ClienteCreatePage />}
                            />
                            <Route
                                path="clientes/:clienteId"
                                element={<ClienteDetailPage />}
                            />
                            <Route
                                path="clientes/:clienteId/editar"
                                element={<ClienteEditPage />}
                            />
                            <Route
                                path="servicios"
                                element={<ServiciosPage />}
                            />
                            <Route
                                path="ordenes-trabajo"
                                element={<OrdenesTrabajoPage />}
                            />
                            <Route
                                path="calendario"
                                element={<CalendarioPage />}
                            />
                            <Route path="facturas" element={<FacturasPage />} />
                            <Route
                                path="facturas/:facturaId"
                                element={<FacturaDetailPage />}
                            />
                            <Route
                                path="facturas/registros"
                                element={<RegistrosFacturacionPage />}
                            />
                            <Route
                                path="facturas/verifactu"
                                element={<VerifactuPage />}
                            />
                            <Route
                                path="inventario"
                                element={<InventarioPage />}
                            />
                            <Route path="informes" element={<InformesPage />} />
                            <Route
                                path="configuracion"
                                element={<ConfiguracionPage />}
                            />
                        </Route>
                    </Route>

                    <Route element={<AdminRoute />}>
                        <Route path="admin" element={<AdminLayout />}>
                            <Route index element={<AdminDashboardPage />} />
                            <Route
                                path="solicitudes"
                                element={<AdminSolicitudesPage />}
                            />
                            <Route
                                path="configuracion"
                                element={<AdminConfiguracionPage />}
                            />
                        </Route>
                    </Route>
                </Route>

                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </BrowserRouter>
    );
}

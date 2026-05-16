import { Route } from "react-router-dom";
import { AdminLayout } from "../../features/admin/layouts/AdminLayout";
import { AuditoriaAdminPage } from "../../features/admin/pages/AuditoriaAdminPage";
import { ConfiguracionGlobalPage } from "../../features/admin/pages/ConfiguracionGlobalPage";
import { AdminDashboardPage } from "../../features/admin/pages/AdminDashboardPage";
import { EmpresasAdminPage } from "../../features/admin/pages/EmpresasAdminPage";
import { AdminSolicitudesPage } from "../../features/admin/pages/AdminSolicitudesPage";
import { UsuariosAdminPage } from "../../features/admin/pages/UsuariosAdminPage";
import { EmpresaDetalleAdminPage } from "../../features/admin/pages/EmpresaDetalleAdminPage";
import { SolicitudDetalleAdminPage } from "../../features/admin/pages/SolicitudDetalleAdminPage";
import { AdminRoute } from "./AdminRoute";
import { PrivateRoute } from "./PrivateRoute";

export const adminRoutes = (
    <Route element={<PrivateRoute />}>
        <Route element={<AdminRoute />}>
            <Route path="admin" element={<AdminLayout />}>
                <Route index element={<AdminDashboardPage />} />
                <Route path="dashboard" element={<AdminDashboardPage />} />
                <Route path="solicitudes-verificacion" element={<AdminSolicitudesPage />} />
                <Route path="solicitudes-verificacion/:id" element={<SolicitudDetalleAdminPage />} />
                <Route path="empresas" element={<EmpresasAdminPage />} />
                <Route path="empresas/:id" element={<EmpresaDetalleAdminPage />} />
                <Route path="usuarios" element={<UsuariosAdminPage />} />
                <Route path="catalogos" element={<ConfiguracionGlobalPage />} />
                <Route path="configuracion" element={<ConfiguracionGlobalPage />} />
                <Route path="auditoria" element={<AuditoriaAdminPage />} />
            </Route>
        </Route>
    </Route>
);

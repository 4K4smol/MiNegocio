import { Navigate, Route } from "react-router-dom";
import { AdminLayout } from "../../features/admin/layouts/AdminLayout";
import { AdminAuditoriaPage } from "../../modules/admin/pages/AdminAuditoriaPage";
import { AdminConfiguracionPage } from "../../modules/admin/pages/AdminConfiguracionPage";
import { AdminDashboardPage } from "../../modules/admin/pages/AdminDashboardPage";
import { AdminEmpresasPage } from "../../modules/admin/pages/AdminEmpresasPage";
import { AdminSolicitudesPage } from "../../modules/admin/pages/AdminSolicitudesPage";
import { AdminUsuariosPage } from "../../modules/admin/pages/AdminUsuariosPage";
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
                <Route path="solicitudes" element={<AdminSolicitudesPage />} />
                <Route path="solicitudes/:id" element={<SolicitudDetalleAdminPage />} />
                <Route path="empresas" element={<AdminEmpresasPage />} />
                <Route path="empresas/:id" element={<EmpresaDetalleAdminPage />} />
                <Route path="usuarios" element={<AdminUsuariosPage />} />
                <Route path="catalogos" element={<AdminConfiguracionPage />} />
                <Route path="configuracion" element={<AdminConfiguracionPage />} />
                <Route path="auditoria" element={<AdminAuditoriaPage />} />
            </Route>
        </Route>
    </Route>
);

import { Outlet } from "react-router-dom";
import { useAuth } from "../../../shared/hooks/useAuth";
import { AdminHeader } from "../components/AdminHeader";
import { AdminSidebar } from "../components/AdminSidebar";
import "../styles/admin.css";
import "../../../modules/admin/admin.css";

export function AdminLayout() {
    const { logout, usuario } = useAuth();

    return (
        <div className="admin-shell">
            <AdminSidebar />
            <div className="admin-main-shell">
                <AdminHeader usuario={usuario} onLogout={logout} />
                <main className="admin-main">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}

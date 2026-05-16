import { adminNavigation } from "../../../app/config/navigation";
import { PrivateAreaLayout } from "../../../shared/components/layout/PrivateAreaLayout";
import { useAuth } from "../../../shared/hooks/useAuth";
import "../styles/admin.css";
import "../styles/admin-theme.css";

export function AdminLayout() {
    const { logout, usuario } = useAuth();

    return (
        <PrivateAreaLayout
            areaLabel="Menú de administración"
            defaultTitle="Administrador"
            navigation={adminNavigation}
            onLogout={logout}
            sidebarSubtitle="Panel administrador"
            topbarEyebrow="Panel privado"
            usuario={usuario}
            userRoleLabel="Administrador"
            variant="admin"
        />
    );
}

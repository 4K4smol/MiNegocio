import { empresaNavigation } from "../app/config/navigation";
import { PrivateAreaLayout } from "../shared/components/layout/PrivateAreaLayout";
import { useAuth } from "../shared/hooks/useAuth";

export function PrivateLayout() {
    const { logout, usuario } = useAuth();

    return (
        <PrivateAreaLayout
            areaLabel="Menú de empresa"
            defaultTitle="Panel de control"
            navigation={empresaNavigation}
            onLogout={logout}
            sidebarSubtitle="CRM empresarial"
            topbarEyebrow="Panel de control"
            usuario={usuario}
            userRoleLabel="Usuario empresa"
            variant="empresa"
        />
    );
}

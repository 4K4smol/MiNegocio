import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";

export function AdminHeader({ usuario, onLogout }) {
    return (
        <header className="admin-header">
            <div>
                <span className="admin-kicker">Panel privado</span>
                <h1>Administrador</h1>
            </div>
            <div className="admin-header-user">
                <div>
                    <strong>{usuario?.name || usuario?.nombre || "Usuario admin"}</strong>
                    <small>Administrador</small>
                </div>
                <button className="admin-button admin-button-ghost" type="button" onClick={onLogout}>
                    <AppIcon icon={appIcons.login} size={17} />
                    Salir
                </button>
            </div>
        </header>
    );
}

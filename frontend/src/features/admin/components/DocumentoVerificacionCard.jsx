import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { EstadoBadge } from "./EstadoBadge";

export function DocumentoVerificacionCard({ documento, onValidar, onRechazar, onVer }) {
    return (
        <article className="admin-card documento-card">
            <div>
                <h3>{documento.nombre}</h3>
                <EstadoBadge estado={documento.estado} />
            </div>
            <p>{documento.descripcion}</p>
            <div className="admin-actions">
                <button className="admin-button admin-button-ghost" type="button" onClick={onVer}>
                    <AppIcon icon={appIcons.ver} size={16} />
                    Ver documento
                </button>
                <button className="admin-button admin-button-success" type="button" onClick={onValidar}>
                    Validar
                </button>
                <button className="admin-button admin-button-danger" type="button" onClick={onRechazar}>
                    Rechazar
                </button>
            </div>
        </article>
    );
}

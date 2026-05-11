import { EstadoBadge } from "./EstadoBadge";

export function ValidacionResumenCard({ solicitud }) {
    return (
        <section className="admin-card validacion-resumen">
            <div>
                <span className="admin-kicker">Solicitud</span>
                <strong>#{solicitud.id}</strong>
            </div>
            <div>
                <span className="admin-kicker">Estado</span>
                <EstadoBadge estado={solicitud.estado} />
            </div>
            <div>
                <span className="admin-kicker">Tipo</span>
                <strong>{solicitud.tipoEmpresa}</strong>
            </div>
            <div>
                <span className="admin-kicker">Fecha</span>
                <strong>{solicitud.fecha}</strong>
            </div>
        </section>
    );
}

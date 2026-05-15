import { StatusBadge } from "../../../shared/components/StatusBadge";

export function EstadoSolicitudBadge({ estado = "pendiente" }) {
    return <StatusBadge status={estado} />;
}

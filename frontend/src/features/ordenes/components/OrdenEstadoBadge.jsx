import { StatusBadge } from "../../../shared/components/StatusBadge";

export function OrdenEstadoBadge({ estado }) {
    return <StatusBadge status={estado || "pendiente"} />;
}


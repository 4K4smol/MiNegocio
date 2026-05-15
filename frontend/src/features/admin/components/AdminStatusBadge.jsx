import { StatusBadge } from "../../../shared/components/StatusBadge";

export function AdminStatusBadge({ estado = "pendiente" }) {
    return <StatusBadge status={estado} />;
}

import { EmptyState as SharedEmptyState } from "../../../shared/components/EmptyState";

export function EmptyState({ title = "Sin resultados", description }) {
    return (
        <SharedEmptyState
            className="admin-card admin-empty-inline"
            description={description}
            title={title}
        />
    );
}

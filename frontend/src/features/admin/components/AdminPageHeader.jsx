import { PageHeader } from "../../../shared/components/PageHeader";

export function AdminPageHeader({
    actions,
    description,
    eyebrow = "Administración",
    title,
}) {
    return (
        <PageHeader
            actions={actions}
            actionsClassName="admin-actions page-actions"
            className="admin-section-header"
            description={description}
            eyebrow={eyebrow}
            headingLevel={2}
            title={title}
        />
    );
}

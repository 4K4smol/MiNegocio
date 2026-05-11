import { AppIcon } from "../../../components/ui/AppIcon";

export function AdminSectionHeader({ title, description, actionLabel, actionIcon, onAction }) {
    return (
        <div className="admin-section-header">
            <div>
                <h2>{title}</h2>
                {description ? <p>{description}</p> : null}
            </div>
            {actionLabel ? (
                <button className="admin-button" type="button" onClick={onAction}>
                    <AppIcon icon={actionIcon} size={17} />
                    {actionLabel}
                </button>
            ) : null}
        </div>
    );
}

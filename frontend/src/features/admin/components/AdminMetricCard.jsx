import { AppIcon } from "../../../components/ui/AppIcon";

export function AdminMetricCard({ title, value, description, icon }) {
    return (
        <article className="admin-card admin-metric-card">
            <span className="admin-metric-icon">
                <AppIcon icon={icon} size={22} />
            </span>
            <div>
                <p>{title}</p>
                <strong>{value}</strong>
                <small>{description}</small>
            </div>
        </article>
    );
}

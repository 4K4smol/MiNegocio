import { AppIcon } from "../../../components/ui/AppIcon";

export function ModuleCard({ icon, title, description }) {
    return (
        <article className="module-card">
            <span className="module-icon" aria-hidden="true">
                <AppIcon icon={icon} size={22} />
            </span>
            <h3>{title}</h3>
            <p>{description}</p>
        </article>
    );
}

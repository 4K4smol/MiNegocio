export function AdminPageHeader({ eyebrow = "Administracion", title, description, actions }) {
    return (
        <header className="admin-section-header">
            <div>
                <span className="admin-kicker">{eyebrow}</span>
                <h2>{title}</h2>
                {description ? <p>{description}</p> : null}
            </div>
            {actions ? <div className="admin-actions">{actions}</div> : null}
        </header>
    );
}

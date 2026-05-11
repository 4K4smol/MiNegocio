export function AdminStatCard({ title, value, description }) {
    return (
        <article className="admin-card admin-metric-card">
            <div>
                <p>{title}</p>
                <strong>{value ?? 0}</strong>
                {description ? <small>{description}</small> : null}
            </div>
        </article>
    );
}

export function PlaceholderPage({ title, description }) {
    return (
        <section className="page">
            <header className="page-header">
                <h1>{title}</h1>
                {description ? <p>{description}</p> : null}
            </header>
        </section>
    );
}

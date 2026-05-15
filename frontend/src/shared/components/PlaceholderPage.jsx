import { PageHeader } from "./PageHeader";

export function PlaceholderPage({ title, description }) {
    return (
        <section className="page">
            <PageHeader description={description} title={title} />
        </section>
    );
}

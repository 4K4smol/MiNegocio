import { ClienteForm } from "../components/ClienteForm";

export function ClienteCreatePage() {
    return (
        <section className="page">
            <header className="page-header">
                <h1>Nuevo cliente</h1>
                <p>Formulario base preparado para clientesService.create.</p>
            </header>
            <ClienteForm />
        </section>
    );
}

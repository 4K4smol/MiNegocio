import { clienteLabel } from "../utils/ordenDisplay";

export function ClienteResumenCard({ cliente, localizacion }) {
    if (!cliente) return null;

    return (
        <aside className="card orden-summary-card">
            <p className="eyebrow">Cliente seleccionado</p>
            <h3>{clienteLabel(cliente)}</h3>
            <dl className="detail-list">
                <div>
                    <dt>DNI/CIF</dt>
                    <dd>{cliente.dni_cif || "-"}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{cliente.email || "-"}</dd>
                </div>
                <div>
                    <dt>Telefono</dt>
                    <dd>{cliente.telefono || "-"}</dd>
                </div>
                <div>
                    <dt>Localizacion</dt>
                    <dd>{localizacion?.direccion_completa || localizacion?.nombre || "Principal o sin especificar"}</dd>
                </div>
            </dl>
        </aside>
    );
}

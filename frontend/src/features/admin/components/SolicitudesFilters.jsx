export function SolicitudesFilters({ filters, onChange, loading }) {
    const update = (key, value) => onChange({ ...filters, [key]: value });

    return (
        <section className="admin-card solicitudes-filters" aria-label="Filtros de solicitudes">
            <label>
                <span>Buscar</span>
                <input
                    value={filters.texto}
                    onChange={(event) => update("texto", event.target.value)}
                    placeholder="Empresa, NIF, responsable o email"
                    disabled={loading}
                />
            </label>
            <label>
                <span>Estado</span>
                <select value={filters.estado} onChange={(event) => update("estado", event.target.value)} disabled={loading}>
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En revisión</option>
                    <option value="subsanacion">Subsanación</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </label>
            <label>
                <span>Tipo</span>
                <select value={filters.tipo_empresa} onChange={(event) => update("tipo_empresa", event.target.value)} disabled={loading}>
                    <option value="">Todos</option>
                    <option value="autonomo">Autonomo</option>
                    <option value="sociedad">Sociedad</option>
                    <option value="pyme">PYME</option>
                </select>
            </label>
            <label>
                <span>Orden</span>
                <select value={filters.orden} onChange={(event) => update("orden", event.target.value)} disabled={loading}>
                    <option value="desc">Mas recientes</option>
                    <option value="asc">Mas antiguas</option>
                </select>
            </label>
        </section>
    );
}

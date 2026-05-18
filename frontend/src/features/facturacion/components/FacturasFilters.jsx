const ESTADO_OPTIONS = [
    { value: '', label: 'Todos los estados' },
    { value: 'borrador', label: 'Borrador' },
    { value: 'emitida', label: 'Emitida' },
    { value: 'pagada', label: 'Pagada' },
    { value: 'anulada', label: 'Anulada' },
    { value: 'rectificada', label: 'Rectificada' },
]

export function FacturasFilters({ filters, onChange }) {
    return (
        <div className="filters-bar">
            <input
                className="input"
                placeholder="Buscar por número o cliente..."
                type="search"
                value={filters.search}
                onChange={(e) => onChange({ ...filters, search: e.target.value })}
            />
            <select
                className="input"
                value={filters.estado}
                onChange={(e) => onChange({ ...filters, estado: e.target.value })}
            >
                {ESTADO_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
            </select>
        </div>
    )
}

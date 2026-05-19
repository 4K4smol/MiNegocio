import { useEffect, useState } from 'react'
import { FilterField, SearchFilters, SearchInput } from '../../../shared/components/SearchFilters'

const ESTADO_OPTIONS = [
    { value: '', label: 'Todos los estados' },
    { value: 'borrador', label: 'Borrador' },
    { value: 'emitida', label: 'Emitida' },
    { value: 'pagada', label: 'Pagada' },
    { value: 'anulada', label: 'Anulada' },
    { value: 'rectificada', label: 'Rectificada' },
]

export function FacturasFilters({ filters, onChange }) {
    const [draftFilters, setDraftFilters] = useState(filters)

    useEffect(() => {
        setDraftFilters(filters)
    }, [filters])

    const updateDraftFilter = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }))
    const resetFilters = () => {
        const nextFilters = { search: '', estado: '' }
        setDraftFilters(nextFilters)
        onChange(nextFilters)
    }

    return (
        <SearchFilters ariaLabel="Filtros de facturas" onReset={resetFilters} onSubmit={() => onChange(draftFilters)}>
            <SearchInput
                label="Buscar"
                placeholder="Numero o cliente"
                value={draftFilters.search}
                onChange={(event) => updateDraftFilter('search', event.target.value)}
            />
            <FilterField label="Estado">
                <select
                    value={draftFilters.estado}
                    onChange={(event) => updateDraftFilter('estado', event.target.value)}
                >
                    {ESTADO_OPTIONS.map((opt) => (
                        <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                </select>
            </FilterField>
        </SearchFilters>
    )
}

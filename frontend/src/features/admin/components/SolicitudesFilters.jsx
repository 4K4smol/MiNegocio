import { useEffect, useState } from "react";
import { FilterField, SearchFilters, SearchInput } from "../../../shared/components/SearchFilters";

const INITIAL_FILTERS = {
    texto: "",
    estado: "",
    tipo_empresa: "",
    orden: "desc",
};

export function SolicitudesFilters({ filters, onChange, loading }) {
    const [draftFilters, setDraftFilters] = useState(filters);

    useEffect(() => {
        setDraftFilters(filters);
    }, [filters]);

    const update = (key, value) => setDraftFilters((current) => ({ ...current, [key]: value }));
    const resetFilters = () => {
        setDraftFilters(INITIAL_FILTERS);
        onChange(INITIAL_FILTERS);
    };

    return (
        <SearchFilters ariaLabel="Filtros de solicitudes" loading={loading} onReset={resetFilters} onSubmit={() => onChange(draftFilters)}>
            <SearchInput
                disabled={loading}
                label="Buscar"
                placeholder="Empresa, NIF, responsable o email"
                value={draftFilters.texto}
                onChange={(event) => update("texto", event.target.value)}
            />
            <FilterField label="Estado">
                <select value={draftFilters.estado} onChange={(event) => update("estado", event.target.value)} disabled={loading}>
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En revision</option>
                    <option value="subsanacion">Subsanacion</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </FilterField>
            <FilterField label="Tipo">
                <select value={draftFilters.tipo_empresa} onChange={(event) => update("tipo_empresa", event.target.value)} disabled={loading}>
                    <option value="">Todos</option>
                    <option value="autonomo">Autonomo</option>
                    <option value="sociedad">Sociedad</option>
                    <option value="pyme">PYME</option>
                </select>
            </FilterField>
            <FilterField label="Orden">
                <select value={draftFilters.orden} onChange={(event) => update("orden", event.target.value)} disabled={loading}>
                    <option value="desc">Mas recientes</option>
                    <option value="asc">Mas antiguas</option>
                </select>
            </FilterField>
        </SearchFilters>
    );
}

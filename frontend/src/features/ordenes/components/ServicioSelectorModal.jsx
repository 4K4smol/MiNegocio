import { useMemo, useState } from "react";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { SearchFilters, SearchInput } from "../../../shared/components/SearchFilters";
import { Modal } from "../../../shared/components/ui/Modal";
import { ServicioSelector } from "./ServicioSelector";

export function ServicioSelectorModal({ onClose, onSelect, open, servicios = [] }) {
    const [search, setSearch] = useState("");
    const [draftSearch, setDraftSearch] = useState("");
    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) return servicios;
        return servicios.filter((servicio) =>
            [servicio.nombre, servicio.codigo, servicio.descripcion]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(term)),
        );
    }, [search, servicios]);

    const resetSearch = () => {
        setDraftSearch("");
        setSearch("");
    };

    return (
        <Modal
            footer={<button className="button button-ghost" type="button" onClick={onClose}>Cerrar</button>}
            open={open}
            size="xl"
            subtitle="Catalogo de servicios"
            title="Anadir servicio"
            onClose={onClose}
        >
            <SearchFilters ariaLabel="Filtros de servicios" onReset={resetSearch} onSubmit={() => setSearch(draftSearch)}>
                <SearchInput
                    label="Buscar"
                    placeholder="Nombre, codigo o descripcion"
                    value={draftSearch}
                    onChange={(event) => setDraftSearch(event.target.value)}
                />
            </SearchFilters>
            <p className="field-help" style={{ marginTop: 4 }}>
                Selecciona un servicio activo y el precio configurado para el tipo de tarifa que corresponda.
            </p>
            <DataTable
                columns={["Servicio", "Unidad", "Tarifa y precio", "Accion"]}
                empty={!filtered.length ? <EmptyState title="No hay servicios disponibles" /> : null}
            >
                {filtered.map((servicio) => (
                    <ServicioSelector
                        key={servicio.id}
                        servicio={servicio}
                        onSelect={(selectedServicio, selectedPrecio) => onSelect(selectedServicio, selectedPrecio)}
                    />
                ))}
            </DataTable>
        </Modal>
    );
}

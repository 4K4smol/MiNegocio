import { useMemo, useState } from "react";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { Modal } from "../../../shared/components/ui/Modal";
import { formatCurrency } from "../../../shared/utils/formatters";

const precioVigente = (servicio) => {
    const precios = servicio.precios || [];
    return precios.find((precio) => !precio.vigente_hasta) || precios[0];
};

export function ServicioSelectorModal({ onClose, onSelect, open, servicios = [] }) {
    const [search, setSearch] = useState("");
    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) return servicios;
        return servicios.filter((servicio) =>
            [servicio.nombre, servicio.codigo, servicio.descripcion]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(term)),
        );
    }, [search, servicios]);

    return (
        <Modal
            footer={<button className="button button-ghost" type="button" onClick={onClose}>Cerrar</button>}
            open={open}
            size="xl"
            subtitle="Catalogo de servicios"
            title="Anadir servicio"
            onClose={onClose}
        >
            <section className="filters-bar">
                <input
                    placeholder="Buscar por nombre, codigo o descripcion"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                />
            </section>
            <p className="field-help" style={{ marginTop: 4 }}>Solo se muestran servicios activos.</p>
            <DataTable
                columns={["Servicio", "Unidad", "Precio vigente", "Accion"]}
                empty={!filtered.length ? <EmptyState title="No hay servicios disponibles" /> : null}
            >
                {filtered.map((servicio) => {
                    const precio = precioVigente(servicio);
                    return (
                        <tr key={servicio.id}>
                            <td>
                                <strong>{servicio.nombre}</strong>
                                {servicio.codigo ? <small>{servicio.codigo}</small> : null}
                            </td>
                            <td>{servicio.unidad_servicio || "unidad"}</td>
                            <td>{formatCurrency(precio?.precio_base)}</td>
                            <td>
                                <button className="button button-ghost" type="button" onClick={() => onSelect(servicio)}>
                                    Anadir
                                </button>
                            </td>
                        </tr>
                    );
                })}
            </DataTable>
        </Modal>
    );
}


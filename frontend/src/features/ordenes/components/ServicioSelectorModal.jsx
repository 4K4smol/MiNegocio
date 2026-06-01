import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { Modal } from "../../../shared/components/ui/Modal";
import { ServicioSelector } from "./ServicioSelector";

export function ServicioSelectorModal({ onClose, onSelect, open, servicios = [] }) {
    return (
        <Modal
            footer={<button className="button button-ghost" type="button" onClick={onClose}>Cerrar</button>}
            open={open}
            size="xl"
            subtitle="Catálogo de servicios"
            title="Añadir servicio"
            onClose={onClose}
        >
            <p className="field-help">
                Selecciona un servicio activo y el precio configurado para el tipo de tarifa que corresponda.
            </p>
            <DataTable
                columns={["Servicio", "Unidad", "Tarifa y precio", "Acción"]}
                empty={!servicios.length ? <EmptyState title="No hay servicios disponibles" /> : null}
            >
                {servicios.map((servicio) => (
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

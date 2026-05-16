import { Badge } from "../../../shared/components/ui/Badge";
import { Modal } from "../../../shared/components/ui/Modal";
import { getClienteDisplayName } from "../utils/clienteForm";

const formatDateTime = (value) =>
    value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value)) : null;

function DetailItem({ label, value }) {
    return (
        <div>
            <dt>{label}</dt>
            <dd>{value || "No indicado"}</dd>
        </div>
    );
}

export function ClienteDetailModal({ cliente, onClose, onEdit }) {
    if (!cliente) return null;

    return (
        <Modal
            footer={(
                <>
                    <button className="button button-ghost" type="button" onClick={onClose}>
                        Cerrar
                    </button>
                    <button className="button" type="button" onClick={() => onEdit?.(cliente)}>
                        Editar
                    </button>
                </>
            )}
            open
            size="lg"
            subtitle={cliente.tipo_cliente?.nombre || "Cliente"}
            title={getClienteDisplayName(cliente)}
            onClose={onClose}
        >
            <dl className="detail-list">
                <DetailItem label="Tipo de cliente" value={cliente.tipo_cliente?.nombre} />
                <DetailItem label="Nombre completo o razon social" value={getClienteDisplayName(cliente)} />
                <DetailItem label="Nombre" value={cliente.nombre} />
                <DetailItem label="Apellidos" value={cliente.apellidos} />
                <DetailItem label="Razon social" value={cliente.razon_social} />
                <DetailItem label="DNI/CIF" value={cliente.dni_cif} />
                <DetailItem label="Telefono" value={cliente.telefono} />
                <DetailItem label="Email" value={cliente.email} />
                <DetailItem label="Persona de contacto" value={cliente.persona_contacto} />
                <div>
                    <dt>Estado</dt>
                    <dd>
                        <Badge tone={cliente.activo ? "success" : "neutral"}>
                            {cliente.activo ? "Activo" : "Inactivo"}
                        </Badge>
                    </dd>
                </div>
                <DetailItem label="Notas" value={cliente.notas} />
                <DetailItem label="Fecha de creacion" value={formatDateTime(cliente.created_at)} />
                <DetailItem label="Ultima actualizacion" value={formatDateTime(cliente.updated_at)} />
            </dl>
        </Modal>
    );
}

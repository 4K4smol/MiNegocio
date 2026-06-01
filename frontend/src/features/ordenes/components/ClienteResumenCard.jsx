import { Mail, MapPin, Phone, UserRound } from "lucide-react";
import { clienteLabel, clienteLocation } from "../utils/ordenDisplay";
import "../styles/ordenes.css";

function ClienteInfoItem({ icon, label, value }) {
    const IconComponent = icon;

    return (
        <div className="cliente-info-item">
            <IconComponent aria-hidden="true" size={16} />
            <span>{label}</span>
            <strong>{value || "-"}</strong>
        </div>
    );
}

export function ClienteResumenCard({ cliente }) {
    if (!cliente) {
        return (
            <aside className="orden-client-preview is-empty">
                <UserRound aria-hidden="true" size={22} />
                <h3>Cliente sin seleccionar</h3>
                <p>Selecciona un cliente para ver sus datos de contacto y ubicación.</p>
            </aside>
        );
    }

    return (
        <aside className="orden-client-preview">
            <p className="eyebrow">Cliente seleccionado</p>
            <h3>{clienteLabel(cliente)}</h3>
            <span className="orden-client-preview__id">{cliente.dni_cif || "Sin DNI/CIF"}</span>
            <div className="orden-client-preview__grid">
                <ClienteInfoItem icon={Mail} label="Email" value={cliente.email} />
                <ClienteInfoItem icon={Phone} label="Teléfono" value={cliente.telefono} />
                <ClienteInfoItem icon={MapPin} label="Ubicación" value={clienteLocation(cliente)} />
            </div>
        </aside>
    );
}

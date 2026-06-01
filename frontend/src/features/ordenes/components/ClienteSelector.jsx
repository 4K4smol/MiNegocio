import { useState } from "react";
import { Link } from "react-router-dom";
import { Building2, CheckCircle2, Mail, MapPin, Phone, Search, UserPlus } from "lucide-react";
import { clienteLabel, clienteLocation, clienteSearchText } from "../utils/ordenDisplay";
import "../styles/ordenes.css";

const normalizeText = (value) =>
    String(value || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

const getError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

export function ClienteSelector({ clientes = [], disabled = false, errors = {}, onChange, value }) {
    const [query, setQuery] = useState("");
    const normalizedQuery = normalizeText(query);
    const selectedCliente = clientes.find((cliente) => String(cliente.id) === String(value));

    const filteredClientes = normalizedQuery
        ? clientes
            .filter((cliente) => normalizeText(clienteSearchText(cliente)).includes(normalizedQuery))
            .slice(0, 12)
        : clientes.slice(0, 8);

    const clienteError = getError(errors, "cliente_id");

    return (
        <section className="cliente-selector" aria-label="Seleccionar cliente">
            <input name="cliente_id" type="hidden" value={value || ""} />

            <div className="cliente-selector__header">
                <div>
                    <h2>Cliente</h2>
                    <p>Busca por nombre, razón social, DNI/CIF, email o teléfono.</p>
                </div>
                <Link className="cliente-selector__new" to="/app/clientes/nuevo">
                    <UserPlus aria-hidden="true" size={17} />
                    Nuevo cliente
                </Link>
            </div>

            <label className="cliente-selector__search">
                <span>Buscar cliente</span>
                <div>
                    <Search aria-hidden="true" size={18} />
                    <input
                        disabled={disabled}
                        placeholder="Empieza a escribir..."
                        type="search"
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                    />
                </div>
            </label>

            {clienteError ? <small className="field-error">{clienteError}</small> : null}

            {selectedCliente ? (
                <div className="cliente-selector__selected" aria-live="polite">
                    <CheckCircle2 aria-hidden="true" size={18} />
                    <span>Seleccionado:</span>
                    <strong>{clienteLabel(selectedCliente)}</strong>
                    <button disabled={disabled} type="button" onClick={() => onChange("")}>
                        Cambiar
                    </button>
                </div>
            ) : null}

            {filteredClientes.length ? (
                <div className="cliente-selector__results">
                    {filteredClientes.map((cliente) => {
                        const selected = String(cliente.id) === String(value);

                        return (
                            <button
                                className={`cliente-result-card ${selected ? "is-selected" : ""}`}
                                disabled={disabled}
                                key={cliente.id}
                                type="button"
                                onClick={() => onChange(String(cliente.id))}
                            >
                                <span className="cliente-result-card__icon" aria-hidden="true">
                                    <Building2 size={18} />
                                </span>
                                <span className="cliente-result-card__content">
                                    <strong>{clienteLabel(cliente)}</strong>
                                    <span>{cliente.dni_cif || "Sin DNI/CIF"}</span>
                                    <span className="cliente-result-card__meta">
                                        {cliente.email ? (
                                            <span>
                                                <Mail aria-hidden="true" size={14} />
                                                {cliente.email}
                                            </span>
                                        ) : null}
                                        {cliente.telefono ? (
                                            <span>
                                                <Phone aria-hidden="true" size={14} />
                                                {cliente.telefono}
                                            </span>
                                        ) : null}
                                        {clienteLocation(cliente) ? (
                                            <span>
                                                <MapPin aria-hidden="true" size={14} />
                                                {clienteLocation(cliente)}
                                            </span>
                                        ) : null}
                                    </span>
                                </span>
                                <span className={`cliente-result-card__status ${cliente.activo === false ? "is-inactive" : ""}`}>
                                    {cliente.activo === false ? "Inactivo" : "Activo"}
                                </span>
                            </button>
                        );
                    })}
                </div>
            ) : (
                <div className="cliente-selector__empty">
                    <strong>No hay clientes que coincidan.</strong>
                    <span>Prueba con otro dato o crea el cliente antes de continuar.</span>
                    <Link className="button button-ghost" to="/app/clientes/nuevo">
                        Crear cliente
                    </Link>
                </div>
            )}
        </section>
    );
}

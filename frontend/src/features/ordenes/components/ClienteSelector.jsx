const clienteLabel = (cliente) =>
    cliente?.razon_social ||
    [cliente?.nombre, cliente?.apellidos].filter(Boolean).join(" ") ||
    cliente?.dni_cif ||
    `Cliente ${cliente?.id}`;

export function ClienteSelector({ clientes = [], disabled = false, errors = {}, localizacionId, onChange, onLocalizacionChange, value }) {
    const selectedCliente = clientes.find((cliente) => String(cliente.id) === String(value));
    const localizaciones = selectedCliente?.localizaciones || [];

    return (
        <>
            <label>
                Cliente
                <select disabled={disabled} name="cliente_id" required value={value} onChange={(event) => onChange(event.target.value)}>
                    <option value="">Selecciona cliente</option>
                    {clientes.map((cliente) => (
                        <option key={cliente.id} value={cliente.id}>
                            {clienteLabel(cliente)}
                        </option>
                    ))}
                </select>
                {errors.cliente_id ? <small className="form-error">{errors.cliente_id[0]}</small> : null}
            </label>

            <label>
                Localizacion
                <select
                    disabled={disabled || !localizaciones.length}
                    name="localizacion_id"
                    value={localizacionId || ""}
                    onChange={(event) => onLocalizacionChange(event.target.value)}
                >
                    <option value="">Principal o sin especificar</option>
                    {localizaciones.map((localizacion) => (
                        <option key={localizacion.id} value={localizacion.id}>
                            {localizacion.nombre || localizacion.direccion || `Localizacion ${localizacion.id}`}
                        </option>
                    ))}
                </select>
                <small>
                    La API actual de ordenes no acepta localizacion_id. Queda preparado para enviarlo cuando exista el campo/end-point.
                </small>
            </label>
        </>
    );
}


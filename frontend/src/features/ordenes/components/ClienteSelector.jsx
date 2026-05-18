import { clienteLabel } from "../utils/ordenDisplay";

export function ClienteSelector({ clientes = [], disabled = false, errors = {}, onChange, onLocalizacionChange, value }) {
    return (
        <label>
            Cliente
            <select
                disabled={disabled}
                name="cliente_id"
                required
                value={value}
                onChange={(event) => {
                    onChange(event.target.value);
                    onLocalizacionChange?.("");
                }}
            >
                <option value="">Selecciona cliente</option>
                {clientes.map((cliente) => (
                    <option key={cliente.id} value={cliente.id}>
                        {clienteLabel(cliente)}
                    </option>
                ))}
            </select>
            {errors.cliente_id ? <small className="form-error">{errors.cliente_id[0]}</small> : null}
        </label>
    );
}

import { localizacionLabel } from "../utils/ordenDisplay";

export function LocalizacionSelector({ cliente, disabled = false, errors = {}, onChange, value }) {
    const localizaciones = (cliente?.localizaciones || []).filter((localizacion) => localizacion.activo !== false);

    return (
        <label>
            Localizacion del cliente
            <select
                disabled={disabled || !cliente || !localizaciones.length}
                name="localizacion_cliente_id"
                value={value || ""}
                onChange={(event) => onChange(event.target.value)}
            >
                <option value="">Principal o sin especificar</option>
                {localizaciones.map((localizacion) => (
                    <option key={localizacion.id} value={localizacion.id}>
                        {localizacionLabel(localizacion)}
                    </option>
                ))}
            </select>
            {errors.localizacion_cliente_id ? <small className="form-error">{errors.localizacion_cliente_id[0]}</small> : null}
            {!localizaciones.length && cliente ? (
                <small className="field-help">Este cliente no tiene localizaciones registradas.</small>
            ) : null}
        </label>
    );
}

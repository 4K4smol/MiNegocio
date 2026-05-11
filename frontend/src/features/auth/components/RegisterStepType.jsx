export function RegisterStepType({ data, errors, onSelect, tiposEmpresa }) {
    return (
        <div className="register-step">
            <div className="choice-grid">
                {tiposEmpresa.map((tipo) => (
                    <label
                        className={
                            String(data.tipo_empresa_id) === String(tipo.id)
                                ? "choice-card choice-card-active"
                                : "choice-card"
                        }
                        key={tipo.id}
                    >
                        <input
                            checked={String(data.tipo_empresa_id) === String(tipo.id)}
                            name="tipo_empresa_id"
                            onChange={() => onSelect(tipo)}
                            type="radio"
                            value={tipo.id}
                        />
                        <span>{tipo.label}</span>
                        <small>{tipo.description}</small>
                    </label>
                ))}
            </div>
            {errors["empresa.tipo_empresa_id"] ? (
                <small className="field-error">{errors["empresa.tipo_empresa_id"]}</small>
            ) : null}
        </div>
    );
}

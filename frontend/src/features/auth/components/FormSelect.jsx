export function FormSelect({
    error,
    label,
    name,
    onChange,
    options,
    placeholder = "Selecciona una opcion",
    required = false,
    value,
}) {
    return (
        <label className="field">
            <span>
                {label}
                {required ? <strong aria-hidden="true"> *</strong> : null}
            </span>
            <select
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${name}-error` : undefined}
                name={name}
                onChange={onChange}
                required={required}
                value={value}
            >
                <option value="">{placeholder}</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {error ? (
                <small className="field-error" id={`${name}-error`}>
                    {error}
                </small>
            ) : null}
        </label>
    );
}

export function FormInput({
    error,
    label,
    name,
    onChange,
    required = false,
    type = "text",
    value,
    ...props
}) {
    return (
        <label className="field">
            <span>
                {label}
                {required ? <strong aria-hidden="true"> *</strong> : null}
            </span>
            <input
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${name}-error` : undefined}
                name={name}
                onChange={onChange}
                required={required}
                type={type}
                value={value}
                {...props}
            />
            {error ? (
                <small className="field-error" id={`${name}-error`}>
                    {error}
                </small>
            ) : null}
        </label>
    );
}

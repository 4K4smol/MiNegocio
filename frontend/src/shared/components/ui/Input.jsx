export function Input({ error, label, name, ...props }) {
    return (
        <label className="field">
            {label ? <span>{label}</span> : null}
            <input aria-invalid={Boolean(error)} id={name} name={name} {...props} />
            {error ? <small className="field-error">{error}</small> : null}
        </label>
    );
}

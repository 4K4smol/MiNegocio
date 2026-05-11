export function Select({ children, error, label, name, ...props }) {
    return (
        <label className="field">
            {label ? <span>{label}</span> : null}
            <select aria-invalid={Boolean(error)} id={name} name={name} {...props}>
                {children}
            </select>
            {error ? <small className="field-error">{error}</small> : null}
        </label>
    );
}

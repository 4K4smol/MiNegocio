const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function MoneyInput({
    currency = "EUR",
    defaultValue,
    disabled,
    error,
    label,
    min = "0",
    name,
    onChange,
    placeholder = "0,00",
    required,
    step = "0.01",
    value,
}) {
    const isControlled = value !== undefined;

    return (
        <label className={joinClasses("field", error ? "field--error" : "")}>
            {label && (
                <span>
                    {label}
                    {required && <span aria-hidden="true" style={{ color: "var(--color-danger)", marginLeft: 2 }}>*</span>}
                </span>
            )}
            <div className="money-input-wrap">
                <span className="money-prefix">{currency}</span>
                <input
                    disabled={disabled}
                    min={min}
                    name={name}
                    placeholder={placeholder}
                    required={required}
                    step={step}
                    type="number"
                    {...(isControlled ? { value, onChange: (e) => onChange?.(e.target.value) } : { defaultValue })}
                />
            </div>
            {error && <small className="field-error">{error}</small>}
        </label>
    );
}

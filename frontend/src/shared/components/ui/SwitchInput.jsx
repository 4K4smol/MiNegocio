import { useState } from "react";

export function SwitchInput({
    checked: controlledChecked,
    defaultChecked = false,
    disabled = false,
    helpText,
    label,
    name,
    onChange,
}) {
    const isControlled = controlledChecked !== undefined;
    const [internalChecked, setInternalChecked] = useState(defaultChecked);
    const checked = isControlled ? controlledChecked : internalChecked;

    const toggle = () => {
        if (disabled) return;
        const next = !checked;
        if (!isControlled) setInternalChecked(next);
        onChange?.(next);
    };

    return (
        <div className="switch-field">
            <button
                aria-checked={checked}
                className={["switch-track", checked ? "is-on" : ""].filter(Boolean).join(" ")}
                disabled={disabled}
                role="switch"
                type="button"
                onClick={toggle}
            >
                <span className="switch-thumb" />
            </button>
            {name && <input name={name} type="hidden" value={checked ? "on" : "off"} />}
            {(label || helpText) && (
                <div className="switch-info">
                    {label && <span className="switch-label">{label}</span>}
                    {helpText && <span className="switch-help">{helpText}</span>}
                </div>
            )}
        </div>
    );
}

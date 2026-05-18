import { useState } from "react";

export function FormSection({
    children,
    className,
    collapsible = false,
    defaultOpen = true,
    description,
    title,
}) {
    const [open, setOpen] = useState(defaultOpen);
    const bodyVisible = !collapsible || open;

    return (
        <div className={["form-section", className].filter(Boolean).join(" ")}>
            <div className="form-section__header">
                <div>
                    <p className="form-section__title">{title}</p>
                    {description && <p className="form-section__description">{description}</p>}
                </div>
                {collapsible && (
                    <button
                        className="form-section__toggle"
                        type="button"
                        onClick={() => setOpen((v) => !v)}
                    >
                        {open ? "Ocultar" : "Mostrar"}
                    </button>
                )}
            </div>
            {bodyVisible && <div className="form-section__body">{children}</div>}
        </div>
    );
}

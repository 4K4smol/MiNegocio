import { useEffect, useId } from "react";

const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function Modal({
    ariaLabel,
    ariaLabelledBy,
    bodyClassName,
    children,
    className,
    closeDisabled = false,
    closeLabel = "Cerrar",
    closeOnBackdrop = true,
    footer,
    isOpen,
    onClose,
    open,
    showCloseButton = true,
    size = "md",
    subtitle,
    title,
}) {
    const titleId = useId();
    const visible = open ?? isOpen;

    useEffect(() => {
        if (!visible || closeDisabled) return;
        const handler = (e) => { if (e.key === "Escape") onClose?.(); };
        document.addEventListener("keydown", handler);
        return () => document.removeEventListener("keydown", handler);
    }, [visible, closeDisabled, onClose]);

    if (!visible) return null;

    const labelledBy = ariaLabelledBy || (title ? titleId : undefined);
    const handleBackdropClick = (event) => {
        if (!closeOnBackdrop || closeDisabled || event.target !== event.currentTarget) return;
        onClose?.();
    };

    return (
        <div className="modal-backdrop" role="presentation" onMouseDown={handleBackdropClick}>
            <section
                aria-label={ariaLabel || (!labelledBy ? title : undefined)}
                aria-labelledby={labelledBy}
                aria-modal="true"
                className={joinClasses("modal", `modal--${size}`, className)}
                role="dialog"
            >
                <header className="modal-header">
                    <div>
                        {subtitle ? <span className="eyebrow">{subtitle}</span> : null}
                        {title ? <h2 id={titleId}>{title}</h2> : null}
                    </div>
                    {showCloseButton && onClose ? (
                        <button className="text-button modal-close" disabled={closeDisabled} type="button" onClick={onClose}>
                            {closeLabel}
                        </button>
                    ) : null}
                </header>
                <div className={joinClasses("modal-body", bodyClassName)}>{children}</div>
                {footer ? <footer className="modal-footer">{footer}</footer> : null}
            </section>
        </div>
    );
}

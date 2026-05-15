const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function ConfirmModal({
    cancelLabel = "Cancelar",
    confirmLabel = "Confirmar",
    description,
    loading = false,
    loadingLabel = "Procesando...",
    onCancel,
    onConfirm,
    open,
    title,
    tone = "danger",
}) {
    if (!open) return null;

    const confirmClassName = joinClasses(
        "button",
        tone === "danger" ? "button-danger" : "",
        tone === "warning" ? "button-warning" : "",
        tone === "success" ? "button-success" : "",
    );

    return (
        <div className="modal-backdrop confirm-modal-backdrop" role="presentation">
            <section className="modal confirm-modal" role="dialog" aria-modal="true" aria-label={title}>
                <header className="modal-header">
                    <div>
                        <span className="eyebrow">Confirmación</span>
                        <h2>{title}</h2>
                    </div>
                </header>
                {description ? <p>{description}</p> : null}
                <footer className="modal-footer">
                    <button className="button button-ghost" disabled={loading} type="button" onClick={onCancel}>
                        {cancelLabel}
                    </button>
                    <button className={confirmClassName} disabled={loading} type="button" onClick={onConfirm}>
                        {loading ? loadingLabel : confirmLabel}
                    </button>
                </footer>
            </section>
        </div>
    );
}

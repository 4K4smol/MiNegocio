import { Modal } from "../ui/Modal";

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
    const confirmClassName = joinClasses(
        "button",
        tone === "danger" ? "button-danger" : "",
        tone === "warning" ? "button-warning" : "",
        tone === "success" ? "button-success" : "",
    );

    return (
        <Modal
            className="confirm-modal"
            closeDisabled={loading}
            closeOnBackdrop={!loading}
            footer={(
                <>
                    <button className="button button-ghost" disabled={loading} type="button" onClick={onCancel}>
                        {cancelLabel}
                    </button>
                    <button className={confirmClassName} disabled={loading} type="button" onClick={onConfirm}>
                        {loading ? loadingLabel : confirmLabel}
                    </button>
                </>
            )}
            open={open}
            showCloseButton={false}
            size="sm"
            subtitle="Confirmacion"
            title={title}
            onClose={onCancel}
        >
            {description ? <p>{description}</p> : null}
        </Modal>
    );
}

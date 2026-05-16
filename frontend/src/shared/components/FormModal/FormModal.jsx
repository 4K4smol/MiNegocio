import { useId } from "react";
import { Modal } from "../ui/Modal";

export function FormModal({
    cancelLabel = "Cancelar",
    cancelClassName = "button button-ghost",
    children,
    closeOnBackdrop = true,
    error,
    loading = false,
    loadingLabel = "Guardando...",
    mode = "create",
    onClose,
    onSubmit,
    open,
    size = "lg",
    submitDisabled = false,
    submitClassName = "button",
    submitLabel,
    subtitle,
    title,
}) {
    const formId = useId();
    const defaultSubmitLabel = mode === "edit" ? "Guardar cambios" : "Crear";
    const currentSubmitLabel = loading ? loadingLabel : (submitLabel || defaultSubmitLabel);

    return (
        <Modal
            closeDisabled={loading}
            closeOnBackdrop={closeOnBackdrop && !loading}
            footer={(
                <>
                    <button className={cancelClassName} disabled={loading} type="button" onClick={onClose}>
                        {cancelLabel}
                    </button>
                    <button className={submitClassName} disabled={loading || submitDisabled} form={formId} type="submit">
                        {currentSubmitLabel}
                    </button>
                </>
            )}
            open={open}
            size={size}
            subtitle={subtitle}
            title={title}
            onClose={onClose}
        >
            <form className="form-modal" id={formId} onSubmit={onSubmit}>
                {error ? <div className="form-error" role="alert">{error}</div> : null}
                {children}
            </form>
        </Modal>
    );
}

import { ConfirmModal } from "../../../shared/components/ConfirmModal";

export function AdminConfirmModal({
    confirmLabel = "Confirmar",
    description,
    loading,
    onCancel,
    onConfirm,
    open,
    title,
}) {
    return (
        <ConfirmModal
            confirmLabel={confirmLabel}
            description={description}
            loading={loading}
            onCancel={onCancel}
            onConfirm={onConfirm}
            open={open}
            title={title}
            tone="danger"
        />
    );
}

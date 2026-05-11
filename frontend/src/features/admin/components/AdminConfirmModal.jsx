export function AdminConfirmModal({ open, title, description, confirmLabel = "Confirmar", loading, onCancel, onConfirm }) {
    if (!open) return null;

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <section className="admin-modal confirm-modal" role="dialog" aria-modal="true">
                <header>
                    <div>
                        <span className="admin-kicker">Confirmacion</span>
                        <h3>{title}</h3>
                    </div>
                </header>
                {description ? <p>{description}</p> : null}
                <footer>
                    <button type="button" className="admin-button admin-button-ghost" onClick={onCancel} disabled={loading}>
                        Cancelar
                    </button>
                    <button type="button" className="admin-button admin-button-danger" onClick={onConfirm} disabled={loading}>
                        {loading ? "Procesando..." : confirmLabel}
                    </button>
                </footer>
            </section>
        </div>
    );
}

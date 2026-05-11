export function Modal({ children, isOpen, title, onClose }) {
    if (!isOpen) return null;

    return (
        <div className="modal-backdrop" role="presentation">
            <section className="modal" role="dialog" aria-modal="true" aria-label={title}>
                <header className="modal-header">
                    <h2>{title}</h2>
                    <button className="text-button modal-close" type="button" onClick={onClose}>
                        Cerrar
                    </button>
                </header>
                {children}
            </section>
        </div>
    );
}

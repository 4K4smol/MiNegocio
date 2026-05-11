export function DocumentoPreviewModal({ preview, loading, error, onClose }) {
    if (!preview && !loading && !error) return null;

    const isImage = preview?.mimeType?.startsWith("image/");
    const isPdf = preview?.mimeType?.includes("application/pdf");

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <section className="admin-modal documento-preview-modal" role="dialog" aria-modal="true" aria-label="Vista previa de documento">
                <header>
                    <div>
                        <span className="admin-kicker">Documento</span>
                        <h3>Vista previa</h3>
                    </div>
                    <button type="button" className="admin-icon-button admin-button-ghost" onClick={onClose} aria-label="Cerrar">
                        X
                    </button>
                </header>
                {loading ? <p className="admin-loading">Abriendo documento...</p> : null}
                {error ? <div className="form-alert">{error}</div> : null}
                {preview?.url && isImage ? <img src={preview.url} alt="Documento de verificacion" /> : null}
                {preview?.url && isPdf ? <iframe title="Documento PDF" src={preview.url} /> : null}
                {preview?.url && !isImage && !isPdf ? (
                    <div className="admin-empty-inline">Este tipo de archivo no se puede previsualizar dentro de la app.</div>
                ) : null}
            </section>
        </div>
    );
}

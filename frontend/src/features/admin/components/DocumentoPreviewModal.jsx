import { Modal } from "../../../shared/components/ui/Modal";

export function DocumentoPreviewModal({ preview, loading, error, onClose }) {
    if (!preview && !loading && !error) return null;

    const isImage = preview?.mimeType?.startsWith("image/");
    const isPdf = preview?.mimeType?.includes("application/pdf");

    return (
        <Modal
            className="documento-preview-modal"
            open
            size="xl"
            subtitle="Documento"
            title="Vista previa"
            onClose={onClose}
        >
            {loading ? <p className="admin-loading">Abriendo documento...</p> : null}
            {error ? <div className="form-alert">{error}</div> : null}
            {preview?.url && isImage ? <img src={preview.url} alt="Documento de verificacion" /> : null}
            {preview?.url && isPdf ? <iframe title="Documento PDF" src={preview.url} /> : null}
            {preview?.url && !isImage && !isPdf ? (
                <div className="admin-empty-inline">Este tipo de archivo no se puede previsualizar dentro de la app.</div>
            ) : null}
        </Modal>
    );
}

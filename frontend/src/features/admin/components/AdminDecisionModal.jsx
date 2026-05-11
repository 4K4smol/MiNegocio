const TITLES = {
    aprobar: "Aprobar solicitud",
    rechazar: "Rechazar solicitud",
    subsanacion: "Pedir subsanacion",
};

const DESCRIPTIONS = {
    aprobar: "Confirma que la documentacion es correcta. Se activaran la empresa y el usuario responsable.",
    rechazar: "Indica el motivo para dejar constancia en el historial de revision.",
    subsanacion: "Indica que documentacion debe aportar o corregir el solicitante.",
};

const OPTIONS = [
    ["identidad", "Identidad"],
    ["empresa_actividad", "Empresa / actividad"],
    ["representacion", "Representacion"],
    ["otros", "Otros"],
];

export function AdminDecisionModal({ type, value, selectedDocuments, loading, error, onChange, onToggleDocument, onClose, onSubmit }) {
    if (!type) return null;

    const requiresMotivo = type !== "aprobar";
    const canSubmit = !loading && (!requiresMotivo || value.trim().length >= 5);

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <form className="admin-modal decision-modal" onSubmit={onSubmit}>
                <header>
                    <div>
                        <span className="admin-kicker">Decision administrativa</span>
                        <h3>{TITLES[type]}</h3>
                    </div>
                    <button type="button" className="admin-icon-button admin-button-ghost" onClick={onClose} aria-label="Cerrar">
                        X
                    </button>
                </header>
                <p>{DESCRIPTIONS[type]}</p>
                <label>
                    <span>{type === "aprobar" ? "Observaciones" : "Motivo obligatorio"}</span>
                    <textarea
                        value={value}
                        onChange={(event) => onChange(event.target.value)}
                        placeholder={type === "aprobar" ? "Documentacion correcta." : "Describe el motivo de la decision"}
                        required={requiresMotivo}
                    />
                </label>
                {type === "subsanacion" ? (
                    <fieldset>
                        <legend>Documentos requeridos</legend>
                        <div className="checkbox-grid">
                            {OPTIONS.map(([id, label]) => (
                                <label key={id}>
                                    <input
                                        type="checkbox"
                                        checked={selectedDocuments.includes(id)}
                                        onChange={() => onToggleDocument(id)}
                                    />
                                    <span>{label}</span>
                                </label>
                            ))}
                        </div>
                    </fieldset>
                ) : null}
                {error ? <div className="form-alert">{error}</div> : null}
                <footer>
                    <button type="button" className="admin-button admin-button-ghost" onClick={onClose} disabled={loading}>
                        Cancelar
                    </button>
                    <button type="submit" className={`admin-button ${type === "rechazar" ? "admin-button-danger" : type === "subsanacion" ? "admin-button-warning" : "admin-button-success"}`} disabled={!canSubmit}>
                        {loading ? "Procesando..." : TITLES[type]}
                    </button>
                </footer>
            </form>
        </div>
    );
}

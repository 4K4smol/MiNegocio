const TITLES = {
    aprobar: "Aprobar solicitud",
    rechazar: "Rechazar solicitud",
    subsanacion: "Pedir subsanacion",
    aprobar_fase: "Aprobar fase",
    rechazar_fase: "Rechazar fase",
};

const DESCRIPTIONS = {
    aprobar: "Confirma que la documentacion es correcta. Se activaran la empresa y el usuario responsable.",
    rechazar: "Indica el motivo para dejar constancia en el historial de revision.",
    subsanacion: "Indica que documentacion debe aportar o corregir el solicitante.",
    aprobar_fase: "Confirma que la documentacion de esta fase es correcta.",
    rechazar_fase: "Indica el motivo para dejar constancia en el historial de revision.",
};

const OPTIONS = [
    ["identidad", "Identidad"],
    ["empresa_actividad", "Empresa / actividad"],
    ["representacion", "Representacion"],
    ["otros", "Otros"],
];

export function AdminDecisionModal({ type, contextLabel, value, selectedDocuments = [], loading, error, onChange, onToggleDocument, onClose, onSubmit }) {
    if (!type) return null;

    const requiresMotivo = !["aprobar", "aprobar_fase"].includes(type);
    const canSubmit = !loading && (!requiresMotivo || value.trim().length >= 5);
    const title = contextLabel ? `${TITLES[type]}: ${contextLabel}` : TITLES[type];

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <form className="admin-modal decision-modal" onSubmit={onSubmit}>
                <header>
                    <div>
                        <span className="admin-kicker">Decision administrativa</span>
                        <h3>{title}</h3>
                    </div>
                    <button type="button" className="admin-icon-button admin-button-ghost" onClick={onClose} aria-label="Cerrar">
                        X
                    </button>
                </header>
                <p>{DESCRIPTIONS[type]}</p>
                <label>
                    <span>{requiresMotivo ? "Motivo obligatorio" : "Observaciones"}</span>
                    <textarea
                        value={value}
                        onChange={(event) => onChange(event.target.value)}
                        placeholder={requiresMotivo ? "Describe el motivo de la decision" : "Documentacion correcta."}
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
                    <button
                        type="submit"
                        className={`admin-button ${type === "rechazar" || type === "rechazar_fase" ? "admin-button-danger" : type === "subsanacion" ? "admin-button-warning" : "admin-button-success"}`}
                        disabled={!canSubmit}
                    >
                        {loading ? "Procesando..." : TITLES[type]}
                    </button>
                </footer>
            </form>
        </div>
    );
}

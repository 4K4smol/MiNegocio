import { FormModal } from "../../../shared/components/FormModal";

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
    const submitClassName = `admin-button ${
        type === "rechazar" || type === "rechazar_fase"
            ? "admin-button-danger"
            : type === "subsanacion"
                ? "admin-button-warning"
                : "admin-button-success"
    }`;

    return (
        <FormModal
            cancelClassName="admin-button admin-button-ghost"
            error={error}
            loading={loading}
            loadingLabel="Procesando..."
            mode="edit"
            open={Boolean(type)}
            size="lg"
            submitClassName={submitClassName}
            submitDisabled={!canSubmit}
            submitLabel={TITLES[type]}
            subtitle="Decision administrativa"
            title={title}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <div className="decision-modal">
                <p>{DESCRIPTIONS[type]}</p>
                <label>
                    <span>{requiresMotivo ? "Motivo obligatorio" : "Observaciones"}</span>
                    <textarea
                        disabled={loading}
                        placeholder={requiresMotivo ? "Describe el motivo de la decision" : "Documentacion correcta."}
                        required={requiresMotivo}
                        value={value}
                        onChange={(event) => onChange(event.target.value)}
                    />
                </label>
                {type === "subsanacion" ? (
                    <fieldset>
                        <legend>Documentos requeridos</legend>
                        <div className="checkbox-grid">
                            {OPTIONS.map(([id, label]) => (
                                <label key={id}>
                                    <input
                                        checked={selectedDocuments.includes(id)}
                                        disabled={loading}
                                        type="checkbox"
                                        onChange={() => onToggleDocument(id)}
                                    />
                                    <span>{label}</span>
                                </label>
                            ))}
                        </div>
                    </fieldset>
                ) : null}
            </div>
        </FormModal>
    );
}

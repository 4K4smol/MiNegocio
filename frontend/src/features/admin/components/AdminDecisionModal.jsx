import { FormModal } from "../../../shared/components/FormModal";

const TITLES = {
    aprobar: "Aprobar solicitud",
    rechazar: "Rechazar solicitud",
    aprobar_fase: "Aprobar fase",
    rechazar_fase: "Rechazar fase",
};

const DESCRIPTIONS = {
    aprobar: "Confirma que la documentacion es correcta. Se activaran la empresa y el usuario responsable.",
    rechazar: "Indica el motivo para dejar constancia en el historial de revision.",
    aprobar_fase: "Confirma que la documentacion de esta fase es correcta.",
    rechazar_fase: "Indica el motivo para dejar constancia en el historial de revision.",
};

export function AdminDecisionModal({ type, contextLabel, value, loading, error, onChange, onClose, onSubmit }) {
    if (!type) return null;

    const requiresMotivo = !["aprobar", "aprobar_fase"].includes(type);
    const canSubmit = !loading && (!requiresMotivo || value.trim().length >= 5);
    const title = contextLabel ? `${TITLES[type]}: ${contextLabel}` : TITLES[type];
    const submitClassName = `admin-button ${
        type === "rechazar" || type === "rechazar_fase"
            ? "admin-button-danger"
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
            </div>
        </FormModal>
    );
}

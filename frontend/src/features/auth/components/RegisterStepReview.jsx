const showValue = (value) => value || "No indicado";

export function RegisterStepReview({
    business,
    confirm,
    documents,
    errors,
    onConfirm,
    requiresRepresentation,
    tipoEmpresaLabel,
    user,
}) {
    return (
        <div className="register-step review-grid">
            <section className="review-card">
                <h3>Responsable</h3>
                <p>{`${showValue(user.nombre)} ${showValue(user.apellido1)}`}</p>
                <span>{showValue(user.email)}</span>
                <span>{showValue(user.telefono)}</span>
            </section>
            <section className="review-card">
                <h3>Empresa / Actividad</h3>
                <p>{showValue(business.nombre_fiscal)}</p>
                <span>{showValue(business.nombre_comercial)}</span>
                <span>{showValue(business.nif)}</span>
                <span>{tipoEmpresaLabel}</span>
            </section>
            <section className="review-card">
                <h3>Documentacion</h3>
                <span>Identidad anverso: {showValue(documents.dni_frontal?.name)}</span>
                <span>Identidad reverso: {showValue(documents.dni_reverso?.name)}</span>
                <span>Selfie: {showValue(documents.selfie?.name)}</span>
                <span>Actividad: {showValue(documents.documento_fiscal?.name)}</span>
                <span>
                    Representacion: {requiresRepresentation ? showValue(documents.documento_representacion?.name) : "No aplica"}
                </span>
                {requiresRepresentation ? (
                    <>
                        <span>Registro mercantil: {showValue(documents.registro_mercantil?.name)}</span>
                        <span>Poder de apoderamiento: {showValue(documents.poder_apoderamiento?.name)}</span>
                    </>
                ) : null}
            </section>
            <section className="review-card">
                <h3>Acceso al CRM</h3>
                <p>El acceso al CRM queda bloqueado hasta la aprobacion administrativa.</p>
                <span>Estado inicial: pendiente de revision</span>
            </section>
            <label className="check-field review-confirm">
                <input checked={confirm} onChange={onConfirm} type="checkbox" />
                <span>Confirmo que la información es correcta.</span>
            </label>
            {errors.confirmReview ? (
                <small className="field-error">{errors.confirmReview}</small>
            ) : null}
        </div>
    );
}

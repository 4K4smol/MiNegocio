const showValue = (value) => value || "No indicado";

export function RegisterStepReview({
    business,
    confirm,
    documents,
    errors,
    onConfirm,
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
                <span>Identidad: {showValue(documents.dni_frontal?.name)}</span>
                <span>Actividad: {showValue(documents.documento_fiscal?.name)}</span>
                <span>
                    Representacion: {showValue(documents.documento_representacion?.name)}
                </span>
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

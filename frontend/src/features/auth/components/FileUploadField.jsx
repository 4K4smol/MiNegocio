const acceptedTypes = ".pdf,.jpg,.jpeg,.png";

export function FileUploadField({ error, help, label, name, onChange, required, value }) {
    return (
        <label className="file-field">
            <span className="file-field-header">
                <span>
                    {label}
                    {required ? <strong aria-hidden="true"> *</strong> : null}
                </span>
                <em>{value ? "Archivo subido" : "Pendiente"}</em>
            </span>
            <input
                accept={acceptedTypes}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${name}-error` : undefined}
                name={name}
                onChange={onChange}
                type="file"
            />
            <span className="file-name">
                {value?.name || "PDF, JPG, JPEG o PNG hasta 10 MB"}
            </span>
            {help ? <small className="field-help">{help}</small> : null}
            {error ? (
                <small className="field-error" id={`${name}-error`}>
                    {error}
                </small>
            ) : null}
        </label>
    );
}

import { FormInput } from "./FormInput";
import { FormSelect } from "./FormSelect";

export function RegisterStepAccess({ data, documentTypes, errors, onChange, onToggleTerms }) {
    return (
        <div className="register-step">
            <div className="form-grid two-columns">
                <FormInput
                    error={errors["usuario.nombre"]}
                    label="Nombre"
                    name="nombre"
                    onChange={onChange}
                    required
                    value={data.nombre}
                />
                <FormInput
                    error={errors["usuario.apellido1"]}
                    label="Primer apellido"
                    name="apellido1"
                    onChange={onChange}
                    required
                    value={data.apellido1}
                />
                <FormInput
                    error={errors["usuario.apellido2"]}
                    label="Segundo apellido"
                    name="apellido2"
                    onChange={onChange}
                    value={data.apellido2}
                />
                <FormInput
                    error={errors["usuario.telefono"]}
                    label="Telefono"
                    name="telefono"
                    onChange={onChange}
                    value={data.telefono}
                />
                <FormInput
                    error={errors["usuario.email"]}
                    label="Correo electronico"
                    name="email"
                    onChange={onChange}
                    required
                    type="email"
                    value={data.email}
                />
                <FormSelect
                    error={errors["usuario.tipo_documento_identidad_id"]}
                    label="Tipo de documento"
                    name="tipo_documento_identidad_id"
                    onChange={onChange}
                    options={documentTypes}
                    required
                    value={data.tipo_documento_identidad_id}
                />
                <FormInput
                    error={errors["usuario.numero_documento"]}
                    label="Numero de documento"
                    name="numero_documento"
                    onChange={onChange}
                    required
                    value={data.numero_documento}
                />
                <FormInput
                    error={errors["usuario.password"]}
                    label="Contrasena"
                    name="password"
                    onChange={onChange}
                    required
                    type="password"
                    value={data.password}
                />
                <FormInput
                    error={errors["usuario.password_confirmation"]}
                    label="Repetir contrasena"
                    name="password_confirmation"
                    onChange={onChange}
                    required
                    type="password"
                    value={data.password_confirmation}
                />
            </div>
            <label className="check-field">
                <input
                    checked={data.termsAccepted}
                    name="termsAccepted"
                    onChange={onToggleTerms}
                    type="checkbox"
                />
                <span>Acepto los terminos y condiciones del servicio.</span>
            </label>
            {errors.termsAccepted ? (
                <small className="field-error">{errors.termsAccepted}</small>
            ) : null}
        </div>
    );
}

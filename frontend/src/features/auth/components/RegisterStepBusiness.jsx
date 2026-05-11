import { FormInput } from "./FormInput";

export function RegisterStepBusiness({ data, errors, onChange }) {
    return (
        <div className="register-step">
            <div className="form-grid two-columns">
                <FormInput
                    error={errors["empresa.nombre_comercial"]}
                    label="Nombre comercial"
                    name="nombre_comercial"
                    onChange={onChange}
                    value={data.nombre_comercial}
                />
                <FormInput
                    error={errors["empresa.nombre_fiscal"]}
                    label="Razon social / nombre fiscal"
                    name="nombre_fiscal"
                    onChange={onChange}
                    required
                    value={data.nombre_fiscal}
                />
                <FormInput
                    error={errors["empresa.nif"]}
                    label="NIF / CIF"
                    name="nif"
                    onChange={onChange}
                    required
                    value={data.nif}
                />
                <FormInput
                    label="Actividad principal"
                    name="actividad_principal"
                    onChange={onChange}
                    value={data.actividad_principal}
                />
                <FormInput
                    label="Sector"
                    name="sector"
                    onChange={onChange}
                    value={data.sector}
                />
                <FormInput
                    error={errors["empresa.direccion_fiscal"]}
                    label="Direccion fiscal"
                    name="direccion_fiscal"
                    onChange={onChange}
                    value={data.direccion_fiscal}
                />
                <FormInput
                    error={errors["empresa.codigo_postal"]}
                    label="Codigo postal"
                    name="codigo_postal"
                    onChange={onChange}
                    value={data.codigo_postal}
                />
                <FormInput
                    error={errors["empresa.municipio"]}
                    label="Localidad"
                    name="municipio"
                    onChange={onChange}
                    value={data.municipio}
                />
                <FormInput
                    error={errors["empresa.provincia"]}
                    label="Provincia"
                    name="provincia"
                    onChange={onChange}
                    value={data.provincia}
                />
                <FormInput
                    error={errors["empresa.telefono"]}
                    label="Telefono de empresa"
                    name="telefono"
                    onChange={onChange}
                    value={data.telefono}
                />
                <FormInput
                    error={errors["empresa.correo"]}
                    label="Email de empresa"
                    name="correo"
                    onChange={onChange}
                    type="email"
                    value={data.correo}
                />
            </div>
        </div>
    );
}

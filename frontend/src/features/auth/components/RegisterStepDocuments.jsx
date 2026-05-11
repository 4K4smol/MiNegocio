import { FileUploadField } from "./FileUploadField";

export function RegisterStepDocuments({
    documents,
    errors,
    isCompany,
    onFileChange,
}) {
    return (
        <div className="register-step">
            <div className="documents-grid">
                <FileUploadField
                    error={errors["documentacion.dni_frontal"]}
                    help="Documento de identidad del responsable."
                    label="DNI/NIE/Pasaporte del responsable"
                    name="dni_frontal"
                    onChange={onFileChange}
                    required
                    value={documents.dni_frontal}
                />
                <FileUploadField
                    error={errors["documentacion.documento_fiscal"]}
                    help="Alta de autonomo, modelo censal o documento fiscal equivalente."
                    label="Documento fiscal o alta de actividad"
                    name="documento_fiscal"
                    onChange={onFileChange}
                    required={isCompany}
                    value={documents.documento_fiscal}
                />
                <FileUploadField
                    error={errors["documentacion.documento_representacion"]}
                    help="Obligatorio para sociedades, empresas o PYMES."
                    label="Documento de representacion/apoderamiento"
                    name="documento_representacion"
                    onChange={onFileChange}
                    required={isCompany}
                    value={documents.documento_representacion}
                />
            </div>
        </div>
    );
}

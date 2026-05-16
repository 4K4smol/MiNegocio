import { FileUploadField } from "./FileUploadField";

export function RegisterStepDocuments({
    documents,
    errors,
    isCompany,
    requiresDniReverse,
    onFileChange,
}) {
    return (
        <div className="register-step">
            <p className="field-help">
                {isCompany
                    ? "Como empresa o sociedad, debes acreditar la identidad del responsable, la existencia de la empresa y la capacidad de representacion o apoderamiento."
                    : "Como autonomo, el responsable es el propio titular de la actividad. Debes acreditar tu identidad y la existencia de tu actividad economica."}
            </p>
            <div className="documents-grid">
                <FileUploadField
                    error={errors["documentacion.dni_frontal"]}
                    help="Anverso del DNI/NIE o pagina principal del pasaporte."
                    label="DNI/NIE/Pasaporte anverso o documento principal"
                    name="dni_frontal"
                    onChange={onFileChange}
                    required
                    value={documents.dni_frontal}
                />
                <FileUploadField
                    error={errors["documentacion.dni_reverso"]}
                    help="Obligatorio para DNI o NIE. Opcional para pasaporte."
                    label="DNI/NIE reverso"
                    name="dni_reverso"
                    onChange={onFileChange}
                    required={requiresDniReverse}
                    value={documents.dni_reverso}
                />
                <FileUploadField
                    error={errors["documentacion.documento_fiscal"]}
                    help="Alta de autonomo, modelo censal o documento fiscal equivalente."
                    label="Documento fiscal o alta de actividad"
                    name="documento_fiscal"
                    onChange={onFileChange}
                    required
                    value={documents.documento_fiscal}
                />
                <FileUploadField
                    error={errors["documentacion.selfie"]}
                    help="Opcional para reforzar la verificacion de identidad."
                    label="Selfie de verificacion"
                    name="selfie"
                    onChange={onFileChange}
                    value={documents.selfie}
                />
                {isCompany ? (
                    <>
                        <FileUploadField
                            error={errors["documentacion.documento_representacion"]}
                            help="Documento que acredita la capacidad del responsable para actuar por la empresa."
                            label="Documento de representacion"
                            name="documento_representacion"
                            onChange={onFileChange}
                            required
                            value={documents.documento_representacion}
                        />
                        <FileUploadField
                            error={errors["documentacion.registro_mercantil"]}
                            help="Opcional, recomendable para sociedades."
                            label="Registro mercantil"
                            name="registro_mercantil"
                            onChange={onFileChange}
                            value={documents.registro_mercantil}
                        />
                        <FileUploadField
                            error={errors["documentacion.poder_apoderamiento"]}
                            help="Opcional, recomendable si el responsable actua como apoderado."
                            label="Poder de apoderamiento"
                            name="poder_apoderamiento"
                            onChange={onFileChange}
                            value={documents.poder_apoderamiento}
                        />
                    </>
                ) : null}
            </div>
        </div>
    );
}

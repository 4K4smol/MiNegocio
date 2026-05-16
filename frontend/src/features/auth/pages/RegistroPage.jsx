import { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { authService } from "../services/authService";
import { RegisterStepAccess } from "../components/RegisterStepAccess";
import { RegisterStepBusiness } from "../components/RegisterStepBusiness";
import { RegisterStepDocuments } from "../components/RegisterStepDocuments";
import { RegisterStepReview } from "../components/RegisterStepReview";
import { RegisterStepType } from "../components/RegisterStepType";
import { StepIndicator } from "../components/StepIndicator";

const steps = ["Acceso", "Tipo", "Empresa", "Documentos", "Revision"];

const fallbackTiposEmpresa = [
    {
        id: 1,
        nombre: "autonomo",
        label: "Autonomo / Profesional individual",
        description: "Alta para profesionales que operan como persona fisica.",
    },
    {
        id: 2,
        nombre: "sociedad",
        label: "Sociedad / PYME",
        description: "Alta para sociedades mercantiles y pequenas empresas.",
    },
];

const fallbackTiposDocumento = [
    { value: 1, label: "DNI" },
    { value: 2, label: "NIE" },
    { value: 3, label: "Pasaporte" },
];

const initialUser = {
    nombre: "",
    apellido1: "",
    apellido2: "",
    telefono: "",
    email: "",
    password: "",
    password_confirmation: "",
    tipo_documento_identidad_id: "",
    numero_documento: "",
    termsAccepted: false,
};

const initialBusiness = {
    tipo_empresa_id: "",
    tipo_empresa_nombre: "",
    nombre_comercial: "",
    nombre_fiscal: "",
    nif: "",
    actividad_principal: "",
    sector: "",
    direccion_fiscal: "",
    codigo_postal: "",
    municipio: "",
    provincia: "",
    telefono: "",
    correo: "",
};

const initialDocuments = {
    dni_frontal: null,
    dni_reverso: null,
    selfie: null,
    documento_fiscal: null,
    registro_mercantil: null,
    documento_representacion: null,
    poder_apoderamiento: null,
};

const firstError = (errors, key) => {
    const value = errors?.[key];
    return Array.isArray(value) ? value[0] : value;
};

const normalizeCollection = (response, fallback) => {
    const records = response?.data?.data || response?.data || [];
    if (!Array.isArray(records) || records.length === 0) return fallback;

    return records.map((record) => ({
        ...record,
        value: record.id,
        label:
            record.descripcion ||
            String(record.nombre || record.id)
                .replace("_", " ")
                .replace(/^\w/, (letter) => letter.toUpperCase()),
        description: record.descripcion || record.nombre,
    }));
};

const normalizeText = (value) =>
    String(value || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim();

const requiresRepresentation = (business) => {
    const name = normalizeText(business.tipo_empresa_nombre);
    if (!name) return String(business.tipo_empresa_id) === "2";

    return !name.includes("autonomo");
};

const documentRequiresBack = (tipoDocumento) => {
    const name = normalizeText(tipoDocumento?.label || tipoDocumento?.nombre);
    return name.includes("dni") || name.includes("nie");
};

const appendIfPresent = (formData, key, value) => {
    if (value === undefined || value === null || value === "") return;
    formData.append(key, value);
};

const buildRegisterFormData = (user, business, documents) => {
    const formData = new FormData();

    [
        "nombre",
        "apellido1",
        "apellido2",
        "telefono",
        "email",
        "password",
        "password_confirmation",
        "tipo_documento_identidad_id",
        "numero_documento",
    ].forEach((field) => {
        appendIfPresent(formData, `usuario[${field}]`, user[field]);
    });

    [
        "tipo_empresa_id",
        "nombre_fiscal",
        "nombre_comercial",
        "nif",
        "correo",
        "telefono",
        "direccion_fiscal",
        "codigo_postal",
        "municipio",
        "provincia",
    ].forEach((field) => {
        appendIfPresent(formData, `empresa[${field}]`, business[field]);
    });

    Object.entries(documents).forEach(([field, file]) => {
        appendIfPresent(formData, `documentacion[${field}]`, file);
    });

    return formData;
};

export function RegistroPage() {
    const [currentStep, setCurrentStep] = useState(1);
    const [user, setUser] = useState(initialUser);
    const [business, setBusiness] = useState(initialBusiness);
    const [documents, setDocuments] = useState(initialDocuments);
    const [confirmReview, setConfirmReview] = useState(false);
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isSuccess, setIsSuccess] = useState(false);
    const [tiposEmpresa, setTiposEmpresa] = useState(fallbackTiposEmpresa);
    const [tiposDocumento, setTiposDocumento] = useState(fallbackTiposDocumento);

    const company = requiresRepresentation(business);

    useEffect(() => {
        let isMounted = true;

        authService
            .getTiposEmpresa()
            .then((response) => {
                if (isMounted) {
                    setTiposEmpresa(normalizeCollection(response, fallbackTiposEmpresa));
                }
            })
            .catch(() => {
                setTiposEmpresa(fallbackTiposEmpresa);
            });

        authService
            .getTiposDocumentoIdentidad()
            .then((response) => {
                if (isMounted) {
                    setTiposDocumento(
                        normalizeCollection(response, fallbackTiposDocumento).map((item) => ({
                            value: item.id || item.value,
                            label: item.label,
                        })),
                    );
                }
            })
            .catch(() => {
                setTiposDocumento(fallbackTiposDocumento);
            });

        return () => {
            isMounted = false;
        };
    }, []);

    const tipoEmpresaLabel = useMemo(() => {
        const tipo = tiposEmpresa.find(
            (item) => String(item.id) === String(business.tipo_empresa_id),
        );
        return tipo?.label || "No indicado";
    }, [business.tipo_empresa_id, tiposEmpresa]);

    const selectedDocumentType = useMemo(
        () => tiposDocumento.find((item) => String(item.value) === String(user.tipo_documento_identidad_id)),
        [tiposDocumento, user.tipo_documento_identidad_id],
    );

    const requiresDniReverse = documentRequiresBack(selectedDocumentType);

    const updateUser = (event) => {
        const { name, value } = event.target;
        setUser((current) => ({ ...current, [name]: value }));
        setErrors((current) => ({ ...current, [`usuario.${name}`]: "", [name]: "" }));
    };

    const updateBusiness = (event) => {
        const { name, value } = event.target;
        setBusiness((current) => ({ ...current, [name]: value }));
        setErrors((current) => ({ ...current, [`empresa.${name}`]: "" }));
    };

    const selectTipoEmpresa = (tipo) => {
        setBusiness((current) => ({
            ...current,
            tipo_empresa_id: tipo.id,
            tipo_empresa_nombre: tipo.nombre || tipo.label,
        }));
        setErrors((current) => ({ ...current, "empresa.tipo_empresa_id": "" }));
    };

    const updateFile = (event) => {
        const { name, files } = event.target;
        setDocuments((current) => ({ ...current, [name]: files?.[0] || null }));
        setErrors((current) => ({ ...current, [`documentacion.${name}`]: "" }));
    };

    const validateStep = (step) => {
        const nextErrors = {};

        if (step === 1) {
            if (!user.nombre.trim()) nextErrors["usuario.nombre"] = "El nombre es obligatorio.";
            if (!user.apellido1.trim()) {
                nextErrors["usuario.apellido1"] = "El primer apellido es obligatorio.";
            }
            if (!user.email.trim()) nextErrors["usuario.email"] = "El email es obligatorio.";
            if (!/^\S+@\S+\.\S+$/.test(user.email)) {
                nextErrors["usuario.email"] = "Introduce un email valido.";
            }
            if (user.password.length < 8) {
                nextErrors["usuario.password"] = "La contrasena debe tener al menos 8 caracteres.";
            }
            if (!/[a-zA-Z]/.test(user.password) || !/\d/.test(user.password)) {
                nextErrors["usuario.password"] =
                    "La contrasena debe incluir letras y numeros.";
            }
            if (user.password !== user.password_confirmation) {
                nextErrors["usuario.password_confirmation"] =
                    "Las contrasenas deben coincidir.";
            }
            if (!user.tipo_documento_identidad_id) {
                nextErrors["usuario.tipo_documento_identidad_id"] =
                    "Selecciona el tipo de documento.";
            }
            if (!user.numero_documento.trim()) {
                nextErrors["usuario.numero_documento"] =
                    "El numero de documento es obligatorio.";
            }
            if (!user.termsAccepted) {
                nextErrors.termsAccepted = "Debes aceptar los terminos.";
            }
        }

        if (step === 2 && !business.tipo_empresa_id) {
            nextErrors["empresa.tipo_empresa_id"] = "Selecciona el tipo de registro.";
        }

        if (step === 3) {
            if (!business.nombre_fiscal.trim()) {
                nextErrors["empresa.nombre_fiscal"] =
                    "La razon social o nombre fiscal es obligatorio.";
            }
            if (!business.nif.trim()) nextErrors["empresa.nif"] = "El NIF/CIF es obligatorio.";
        }

        if (step === 4) {
            if (!documents.dni_frontal) {
                nextErrors["documentacion.dni_frontal"] =
                    "Adjunta el anverso o documento principal de identidad.";
            }
            if (requiresDniReverse && !documents.dni_reverso) {
                nextErrors["documentacion.dni_reverso"] =
                    "Adjunta el reverso del DNI/NIE.";
            }
            if (!documents.documento_fiscal) {
                nextErrors["documentacion.documento_fiscal"] =
                    "Adjunta el documento fiscal o alta de actividad.";
            }
            if (company && !documents.documento_representacion) {
                nextErrors["documentacion.documento_representacion"] =
                    "Adjunta el documento de representacion.";
            }
        }

        if (step === 5 && !confirmReview) {
            nextErrors.confirmReview = "Confirma que la información es correcta.";
        }

        setErrors(nextErrors);
        return Object.keys(nextErrors).length === 0;
    };

    const goNext = () => {
        setMessage("");
        if (validateStep(currentStep)) {
            setCurrentStep((step) => Math.min(step + 1, steps.length));
        }
    };

    const goBack = () => {
        setMessage("");
        setErrors({});
        setCurrentStep((step) => Math.max(step - 1, 1));
    };

    const submitRegister = async () => {
        setMessage("");
        if (!validateStep(5)) return;

        setIsSubmitting(true);
        try {
            await authService.register(buildRegisterFormData(user, business, documents));
            setIsSuccess(true);
        } catch (error) {
            const backendErrors = {};
            Object.keys(error?.errors || {}).forEach((key) => {
                backendErrors[key] = firstError(error.errors, key);
            });
            setErrors(backendErrors);
            setMessage(error?.message || "No se ha podido enviar la solicitud.");
        } finally {
            setIsSubmitting(false);
        }
    };

    const renderStep = () => {
        if (currentStep === 1) {
            return (
                <RegisterStepAccess
                    data={user}
                    documentTypes={tiposDocumento}
                    errors={errors}
                    onChange={updateUser}
                    onToggleTerms={(event) =>
                        setUser((current) => ({
                            ...current,
                            termsAccepted: event.target.checked,
                        }))
                    }
                />
            );
        }

        if (currentStep === 2) {
            return (
                <RegisterStepType
                    data={business}
                    errors={errors}
                    onSelect={selectTipoEmpresa}
                    tiposEmpresa={tiposEmpresa}
                />
            );
        }

        if (currentStep === 3) {
            return (
                <RegisterStepBusiness
                    data={business}
                    errors={errors}
                    onChange={updateBusiness}
                />
            );
        }

        if (currentStep === 4) {
            return (
                <RegisterStepDocuments
                    documents={documents}
                    errors={errors}
                    isCompany={company}
                    requiresDniReverse={requiresDniReverse}
                    onFileChange={updateFile}
                />
            );
        }

        return (
            <RegisterStepReview
                business={business}
                confirm={confirmReview}
                documents={documents}
                errors={errors}
                onConfirm={(event) => setConfirmReview(event.target.checked)}
                requiresRepresentation={company}
                tipoEmpresaLabel={tipoEmpresaLabel}
                user={user}
            />
        );
    };

    if (isSuccess) {
        return (
            <section className="auth-page register-page">
                <div className="auth-panel success-panel">
                    <span className="eyebrow">Solicitud enviada</span>
                    <h1>Solicitud enviada correctamente</h1>
                    <p>
                        Revisaremos la documentacion y te notificaremos el estado
                        por correo electronico.
                    </p>
                    <Link className="button" to="/login">
                        <AppIcon icon={appIcons.login} size={18} />
                        Ir a iniciar sesión
                    </Link>
                </div>
            </section>
        );
    }

    return (
        <section className="auth-page register-page">
            <div className="register-panel">
                <div className="register-heading">
                    <div>
                        <span className="eyebrow">Alta de MiNegocio</span>
                        <h1>Registro en 5 pasos</h1>
                        <p>
                            Completa los datos del responsable, la empresa y la
                            documentacion de verificacion.
                        </p>
                    </div>
                    <Link to="/">Volver a inicio</Link>
                </div>

                <StepIndicator currentStep={currentStep} steps={steps} />
                {message ? <div className="form-alert">{message}</div> : null}

                <div className="register-card">{renderStep()}</div>

                <div className="wizard-actions">
                    <button
                        className="button button-ghost"
                        disabled={currentStep === 1 || isSubmitting}
                        onClick={goBack}
                        type="button"
                    >
                        Anterior
                    </button>
                    {currentStep < steps.length ? (
                        <button className="button" onClick={goNext} type="button">
                            <AppIcon icon={appIcons.crear} size={18} />
                            Siguiente
                        </button>
                    ) : (
                        <button
                            className="button"
                            disabled={isSubmitting}
                            onClick={submitRegister}
                            type="button"
                        >
                            <AppIcon icon={appIcons.validar} size={18} />
                            {isSubmitting ? "Enviando..." : "Enviar solicitud"}
                        </button>
                    )}
                </div>
            </div>
        </section>
    );
}

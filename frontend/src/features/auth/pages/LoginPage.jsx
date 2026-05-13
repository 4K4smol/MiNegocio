import { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { useAuth } from "../../../shared/hooks/useAuth";
import { FormInput } from "../components/FormInput";

const getErrorMessage = (error) => {
    if (error?.isUnauthorized?.()) return "Credenciales incorrectas.";
    if (error?.isForbidden?.()) {
        return (
            error.message ||
            "Tu cuenta está pendiente de validación o se encuentra inactiva."
        );
    }
    if (error?.status >= 500) {
        return "Error de servidor. Intentalo de nuevo mas tarde.";
    }
    return error?.message || "No se ha podido iniciar sesión.";
};

const getRedirectTarget = (from) => {
    if (!from) return "/app";
    if (typeof from === "string") return from;
    return `${from.pathname || "/app"}${from.search || ""}${from.hash || ""}`;
};

export function LoginPage() {
    const [form, setForm] = useState({ email: "", password: "" });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const [message, setMessage] = useState(location.state?.message || "");

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
        setErrors((current) => ({ ...current, [name]: "" }));
    };

    const validate = () => {
        const nextErrors = {};
        if (!form.email.trim()) nextErrors.email = "El correo es obligatorio.";
        if (!form.password) nextErrors.password = "La contraseña es obligatoria.";
        setErrors(nextErrors);
        return Object.keys(nextErrors).length === 0;
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setMessage("");
        if (!validate()) return;

        setIsSubmitting(true);
        try {
            const response = await login({
                email: form.email,
                password: form.password,
                device_name: "MiNegocio Web",
            });
            const destination = getRedirectTarget(location.state?.from);

            navigate("/splash", {
                replace: true,
                state: {
                    destination,
                    loginSession: response?.data || null,
                },
            });
        } catch (error) {
            setMessage(getErrorMessage(error));
            if (error?.errors) {
                setErrors({
                    email: error.errors.email?.[0] || "",
                    password: error.errors.password?.[0] || "",
                });
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <section className="auth-page">
            <div className="auth-panel">
                <span className="eyebrow">Área de gestión</span>
                <h1>Iniciar sesión</h1>
                <p>
                    Accede al panel privado para gestionar clientes, ordenes,
                    facturas e informes.
                </p>
                {message ? <div className="form-alert">{message}</div> : null}
                <form className="auth-form" onSubmit={handleSubmit}>
                    <FormInput
                        autoComplete="email"
                        error={errors.email}
                        label="Correo electronico"
                        name="email"
                        onChange={handleChange}
                        required
                        type="email"
                        value={form.email}
                    />
                    <FormInput
                        autoComplete="current-password"
                        error={errors.password}
                        label="Contraseña"
                        name="password"
                        onChange={handleChange}
                        required
                        type="password"
                        value={form.password}
                    />
                    <button className="button" disabled={isSubmitting} type="submit">
                        <AppIcon icon={appIcons.login} size={18} />
                        {isSubmitting ? "Accediendo..." : "Acceder"}
                    </button>
                </form>
                <div className="auth-links">
                    <Link to="/registro">Crear cuenta</Link>
                    <Link to="/">Volver a la página pública</Link>
                </div>
            </div>
            <aside className="auth-side">
                <strong>MiNegocio</strong>
                <span>Operaciones, facturación e informes conectados.</span>
            </aside>
        </section>
    );
}

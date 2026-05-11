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
            "Tu cuenta esta pendiente de validacion o se encuentra inactiva."
        );
    }
    if (error?.status >= 500) return "Error de servidor. Intentalo de nuevo mas tarde.";
    return error?.message || "No se ha podido iniciar sesion.";
};

const isAdminSession = (session) =>
    session?.role?.nombre === "admin" ||
    session?.user?.role?.nombre === "admin" ||
    session?.usuario?.role?.nombre === "admin" ||
    session?.role === "admin" ||
    session?.rol === "admin";

export function LoginPage() {
    const [form, setForm] = useState({ email: "", password: "" });
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
        setErrors((current) => ({ ...current, [name]: "" }));
    };

    const validate = () => {
        const nextErrors = {};
        if (!form.email.trim()) nextErrors.email = "El correo es obligatorio.";
        if (!form.password) nextErrors.password = "La contrasena es obligatoria.";
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
            const session = response?.data;

            if (isAdminSession(session)) {
                navigate("/admin", { replace: true });
                return;
            }

            if (session?.puede_acceder_crm !== true) {
                setMessage("Tu cuenta esta pendiente de validacion administrativa.");
                return;
            }

            const destination = location.state?.from?.pathname || "/app";

            navigate(destination, { replace: true });
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
                <span className="eyebrow">Area de Gestion</span>
                <h1>Iniciar sesion</h1>
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
                        label="Contrasena"
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
                    <Link to="/">Volver a la pagina publica</Link>
                </div>
            </div>
            <aside className="auth-side">
                <strong>MiNegocio</strong>
                <span>Operaciones, facturacion e informes conectados.</span>
            </aside>
        </section>
    );
}

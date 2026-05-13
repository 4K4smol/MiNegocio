import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { hasRole } from "../../../app/router/roleUtils";
import { useAuth } from "../../../shared/hooks/useAuth";

const MIN_SPLASH_TIME = 1200;
const PENDING_VALIDATION_MESSAGE =
    "Tu cuenta está pendiente de validación administrativa.";

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

const resolveUser = (session, usuario) =>
    usuario || session?.user || session?.usuario || null;

const hasEmpresaAccess = (usuario, session) =>
    Boolean(
        usuario?.empresa_id ||
            usuario?.empresaId ||
            session?.empresa ||
            session?.empresa_id ||
            session?.empresaId,
    );

const canAccessCrm = (usuario, session) =>
    session?.puede_acceder_crm === true ||
    session?.puedeAccederCrm === true ||
    usuario?.puede_acceder_crm === true ||
    usuario?.puedeAccederCrm === true;

const isAdminSession = (usuario, session) =>
    hasRole(usuario, session, ["admin"]) ||
    session?.role?.nombre === "admin" ||
    usuario?.role?.nombre === "admin" ||
    usuario?.rol === "admin" ||
    session?.rol === "admin" ||
    usuario?.role === "admin" ||
    usuario?.tipo === "admin" ||
    session?.role === "admin" ||
    session?.rol?.nombre === "admin";

const resolveDestination = ({ destination, isAdmin, usuario, session }) => {
    if (isAdmin) return "/admin";
    if (!canAccessCrm(usuario, session)) return "/login";
    if (!hasEmpresaAccess(usuario, session)) return "/registro";
    if (!destination || destination === "/login" || destination === "/splash") {
        return "/app";
    }
    if (destination.startsWith("/admin")) return "/app";
    return destination;
};

export function SplashPage() {
    const { refreshSession, session, usuario } = useAuth();
    const location = useLocation();
    const navigate = useNavigate();
    const [status, setStatus] = useState("Validando sesión...");
    const [initialData] = useState(() => ({
            destination: location.state?.destination,
            loginSession: location.state?.loginSession || session,
            usuario,
    }));

    useEffect(() => {
        let isActive = true;

        const prepareSession = async () => {
            try {
                setStatus("Cargando datos...");
                const [nextSession] = await Promise.all([
                    refreshSession(),
                    delay(MIN_SPLASH_TIME),
                ]);

                if (!isActive) return;

                const { destination, loginSession, usuario: initialUser } =
                    initialData;
                const activeSession = nextSession || loginSession;
                const activeUser = resolveUser(activeSession, initialUser);
                const isAdmin = isAdminSession(activeUser, activeSession);
                const nextDestination = resolveDestination({
                    destination,
                    isAdmin,
                    usuario: activeUser,
                    session: activeSession,
                });

                if (nextDestination === "/login") {
                    navigate("/login", {
                        replace: true,
                        state: { message: PENDING_VALIDATION_MESSAGE },
                    });
                    return;
                }

                navigate(nextDestination, { replace: true });
            } catch {
                if (!isActive) return;
                navigate("/login", {
                    replace: true,
                    state: {
                        message:
                            "No se han podido cargar tus datos. Inicia sesión de nuevo.",
                    },
                });
            }
        };

        prepareSession();

        return () => {
            isActive = false;
        };
    }, [initialData, navigate, refreshSession]);

    return (
        <main className="splash-page" aria-live="polite">
            <section className="splash-panel">
                <img
                    alt="MiNegocio"
                    className="splash-logo"
                    src="/assets/brand/minegocio-logo-horizontal-transparente.png"
                />
                <div className="splash-loader" aria-hidden="true">
                    <span />
                </div>
                <p>{status}</p>
            </section>
        </main>
    );
}

export { ApiError } from "./apiError";

export const getApiErrorMessage = (
    error,
    fallback = "No se ha podido completar la operacion.",
) => {
    if (error?.isUnauthorized?.()) return "Credenciales incorrectas.";
    if (error?.isForbidden?.()) return error.message || "No tienes permisos.";
    if (error?.status >= 500) return "Error de servidor. Intentalo de nuevo mas tarde.";
    return error?.message || fallback;
};

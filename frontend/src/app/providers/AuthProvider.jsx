import { useCallback, useEffect, useMemo, useState } from "react";
import { authService } from "../../features/auth/services/authService";
import {
    clearAuthStorage,
    getSession,
    getToken,
} from "../../shared/api";
import { AuthContext } from "./authContext";

const resolveUser = (session) => session?.user || session?.usuario || null;

export function AuthProvider({ children }) {
    const [token, setTokenState] = useState(() => getToken());
    const [session, setSessionState] = useState(() => getSession());
    const [isLoading, setIsLoading] = useState(false);

    const usuario = resolveUser(session);
    const isAuthenticated = Boolean(token);

    useEffect(() => {
        setTokenState(getToken());
        setSessionState(getSession());
    }, []);

    const refreshSession = useCallback(async () => {
        if (!getToken()) return null;

        setIsLoading(true);
        try {
            const response = await authService.me();
            const nextSession = response?.data || null;
            setSessionState(nextSession);
            return nextSession;
        } finally {
            setIsLoading(false);
        }
    }, []);

    const login = useCallback(async (credentials) => {
        const response = await authService.login(credentials);
        setTokenState(getToken());
        setSessionState(getSession());
        return response;
    }, []);

    const logout = useCallback(async () => {
        try {
            await authService.logout();
        } finally {
            clearAuthStorage();
            setTokenState(null);
            setSessionState(null);
        }
    }, []);

    const value = useMemo(
        () => ({
            token,
            usuario,
            session,
            isAuthenticated,
            isLoading,
            login,
            logout,
            refreshSession,
        }),
        [
            token,
            usuario,
            session,
            isAuthenticated,
            isLoading,
            login,
            logout,
            refreshSession,
        ],
    );

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
}

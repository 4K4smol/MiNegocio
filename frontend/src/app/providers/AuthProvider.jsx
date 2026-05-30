import { useCallback, useEffect, useMemo, useState } from "react";
import { authService } from "../../features/auth/services/authService";
import {
    clearAuthStorage,
    getSession,
    getToken,
    setSession,
} from "../../shared/api";
import { AuthContext } from "./authContext";

const resolveUser = (session) => session?.user || session?.usuario || null;

export function AuthProvider({ children }) {
    const [token, setTokenState] = useState(() => getToken());
    const [session, setSessionState] = useState(() => getSession());
    const [isInitialized, setIsInitialized] = useState(() => !getToken());
    const [isLoading, setIsLoading] = useState(() => Boolean(getToken()));

    const usuario = resolveUser(session);
    const isAuthenticated = Boolean(token);

    const refreshSession = useCallback(async () => {
        const currentToken = getToken();

        if (!currentToken) {
            setTokenState(null);
            setSessionState(null);
            return null;
        }

        setTokenState(currentToken);
        setIsLoading(true);
        try {
            const response = await authService.me();
            const nextSession = response?.data || null;
            setSessionState(nextSession);
            if (nextSession) setSession(nextSession);
            return nextSession;
        } catch (error) {
            if (error?.status === 401 || error?.isUnauthorized?.()) {
                clearAuthStorage();
                setTokenState(null);
                setSessionState(null);
            }
            throw error;
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        let isMounted = true;

        const initializeSession = async () => {
            if (!getToken()) {
                if (isMounted) {
                    setTokenState(null);
                    setSessionState(null);
                    setIsLoading(false);
                    setIsInitialized(true);
                }
                return;
            }

            try {
                await refreshSession();
            } catch {
                // Invalid tokens are cleared in refreshSession. Other failures keep
                // the stored session so the user is not logged out by a transient error.
            } finally {
                if (isMounted) {
                    setIsInitialized(true);
                }
            }
        };

        initializeSession();

        return () => {
            isMounted = false;
        };
    }, [refreshSession]);

    const login = useCallback(async (credentials) => {
        const response = await authService.login(credentials);
        setTokenState(getToken());
        setSessionState(getSession());
        setIsInitialized(true);
        return response;
    }, []);

    const logout = useCallback(async () => {
        try {
            await authService.logout();
        } finally {
            clearAuthStorage();
            setTokenState(null);
            setSessionState(null);
            setIsInitialized(true);
        }
    }, []);

    const value = useMemo(
        () => ({
            token,
            usuario,
            session,
            isAuthenticated,
            isInitialized,
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
            isInitialized,
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

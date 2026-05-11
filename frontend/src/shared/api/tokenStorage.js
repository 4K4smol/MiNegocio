const TOKEN_KEY = 'minegocio_token'
const SESSION_KEY = 'minegocio_session'

const canUseLocalStorage = () => typeof localStorage !== 'undefined'

export const getToken = () => {
    if (!canUseLocalStorage()) return null
    return localStorage.getItem(TOKEN_KEY)
}

export const setToken = (token) => {
    if (!canUseLocalStorage()) return
    localStorage.setItem(TOKEN_KEY, token)
}

export const clearToken = () => {
    if (!canUseLocalStorage()) return
    localStorage.removeItem(TOKEN_KEY)
}

export const getSession = () => {
    if (!canUseLocalStorage()) return null

    const session = localStorage.getItem(SESSION_KEY)
    if (!session) return null

    try {
        return JSON.parse(session)
    } catch {
        clearSession()
        return null
    }
}

export const setSession = (session) => {
    if (!canUseLocalStorage()) return
    localStorage.setItem(SESSION_KEY, JSON.stringify(session))
}

export const clearSession = () => {
    if (!canUseLocalStorage()) return
    localStorage.removeItem(SESSION_KEY)
}

export const clearAuthStorage = () => {
    clearToken()
    clearSession()
}

export const tokenStorage = {
    getToken,
    setToken,
    clearToken,
    getSession,
    setSession,
    clearSession,
    clearAuthStorage,
}


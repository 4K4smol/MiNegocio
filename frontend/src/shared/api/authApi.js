import { objectToFormData } from './formData'
import { apiRequest } from './httpClient'
import { endpoints } from './endpoints'
import { clearAuthStorage, setSession, setToken } from './tokenStorage'

export const authApi = {
    login: async (credentials) => {
        const response = await apiRequest(endpoints.auth.login, {
            method: 'POST',
            body: credentials,
            auth: false,
        })

        if (response?.data?.token) {
            setToken(response.data.token)
            setSession(response.data)
        }

        return response
    },

    register: (data) =>
        apiRequest(endpoints.auth.register, {
            method: 'POST',
            body: data instanceof FormData ? data : objectToFormData(data),
            auth: false,
        }),

    getTiposEmpresa: () =>
        apiRequest(endpoints.catalogos.tiposEmpresa, {
            auth: false,
        }),

    getTiposDocumentoIdentidad: () =>
        apiRequest(endpoints.catalogos.tiposDocumentoIdentidad, {
            auth: false,
        }),

    me: () => apiRequest(endpoints.auth.me),

    logout: async () => {
        try {
            return await apiRequest(endpoints.auth.logout, {
                method: 'POST',
            })
        } finally {
            clearAuthStorage()
        }
    },
}

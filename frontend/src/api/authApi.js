import { objectToFormData } from './formData'
import { apiRequest } from './httpClient'
import { clearAuthStorage, setSession, setToken } from './tokenStorage'

export const authApi = {
    login: async (credentials) => {
        const response = await apiRequest('auth/login', {
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
        apiRequest('auth/register', {
            method: 'POST',
            body: objectToFormData(data),
            auth: false,
        }),

    me: () => apiRequest('auth/me'),

    logout: async () => {
        try {
            return await apiRequest('auth/logout', {
                method: 'POST',
            })
        } finally {
            clearAuthStorage()
        }
    },
}


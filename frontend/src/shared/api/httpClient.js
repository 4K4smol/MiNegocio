import { API_BASE_URL } from './apiConfig'
import { ApiError } from './apiError'
import { clearAuthStorage, getToken } from './tokenStorage'

const isFormData = (value) =>
    typeof FormData !== 'undefined' && value instanceof FormData

const normalizeBaseUrl = (url) => url.replace(/\/+$/, '')
const normalizePath = (path) => String(path).replace(/^\/+/, '')

const appendParam = (searchParams, key, value) => {
    if (value === undefined || value === null || value === '') return

    if (Array.isArray(value)) {
        value.forEach((item) => appendParam(searchParams, `${key}[]`, item))
        return
    }

    if (typeof value === 'object') {
        Object.entries(value).forEach(([childKey, childValue]) => {
            appendParam(searchParams, `${key}[${childKey}]`, childValue)
        })
        return
    }

    searchParams.append(key, value)
}

export const buildApiUrl = (path, params) => {
    const url = new URL(
        `${normalizeBaseUrl(API_BASE_URL)}/${normalizePath(path)}`,
    )

    Object.entries(params || {}).forEach(([key, value]) => {
        appendParam(url.searchParams, key, value)
    })

    return url.toString()
}

const parseJsonResponse = async (response) => {
    const contentType = response.headers.get('content-type') || ''
    if (!contentType.includes('application/json')) return null

    return response.json()
}

const createApiError = (response, payload) =>
    new ApiError(payload?.message || response.statusText || 'Error de API', {
        status: response.status,
        errors: payload?.errors ?? null,
        payload,
    })

export const apiRequest = async (path, options = {}) => {
    const {
        method = 'GET',
        params,
        body,
        auth = true,
        headers = {},
        signal,
    } = options

    const requestHeaders = new Headers(headers)
    requestHeaders.set('Accept', 'application/json')

    if (auth) {
        const token = getToken()
        if (token) requestHeaders.set('Authorization', `Bearer ${token}`)
    }

    const requestOptions = {
        method,
        headers: requestHeaders,
        signal,
    }

    if (body !== undefined) {
        if (isFormData(body)) {
            requestOptions.body = body
        } else {
            requestHeaders.set('Content-Type', 'application/json')
            requestOptions.body = JSON.stringify(body)
        }
    }

    const response = await fetch(buildApiUrl(path, params), requestOptions)

    if (response.status === 204) {
        return {
            success: true,
            message: '',
            data: null,
        }
    }

    const payload = await parseJsonResponse(response)

    if (!response.ok || payload?.success === false) {
        if (response.status === 401) clearAuthStorage()
        throw createApiError(response, payload)
    }

    return payload
}


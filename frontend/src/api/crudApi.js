import { apiRequest } from './httpClient'

export const createCrudApi = (resource) => ({
    list: (params) => apiRequest(resource, { params }),
    get: (id) => apiRequest(`${resource}/${id}`),
    create: (data) =>
        apiRequest(resource, {
            method: 'POST',
            body: data,
        }),
    update: (id, data) =>
        apiRequest(`${resource}/${id}`, {
            method: 'PUT',
            body: data,
        }),
    remove: (id) =>
        apiRequest(`${resource}/${id}`, {
            method: 'DELETE',
        }),
})


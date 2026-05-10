export class ApiError extends Error {
    constructor(message, { status = null, errors = null, payload = null } = {}) {
        super(message)
        this.name = 'ApiError'
        this.status = status
        this.errors = errors
        this.payload = payload
    }

    isValidationError() {
        return this.status === 422
    }

    isUnauthorized() {
        return this.status === 401
    }

    isForbidden() {
        return this.status === 403
    }
}


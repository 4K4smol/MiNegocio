const isFile = (value) => typeof File !== 'undefined' && value instanceof File
const isBlob = (value) => typeof Blob !== 'undefined' && value instanceof Blob
const isDate = (value) => value instanceof Date

const appendValue = (formData, key, value) => {
    if (value === undefined) return

    if (value === null) {
        formData.append(key, '')
        return
    }

    if (isFile(value) || isBlob(value)) {
        formData.append(key, value)
        return
    }

    if (isDate(value)) {
        formData.append(key, value.toISOString())
        return
    }

    if (Array.isArray(value)) {
        value.forEach((item, index) => appendValue(formData, `${key}[${index}]`, item))
        return
    }

    if (typeof value === 'object') {
        Object.entries(value).forEach(([childKey, childValue]) => {
            appendValue(formData, `${key}[${childKey}]`, childValue)
        })
        return
    }

    formData.append(key, value)
}

export const objectToFormData = (data) => {
    const formData = new FormData()

    Object.entries(data || {}).forEach(([key, value]) => {
        appendValue(formData, key, value)
    })

    return formData
}


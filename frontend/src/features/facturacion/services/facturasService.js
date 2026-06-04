import { apiRequest, buildApiUrl, endpoints, getToken } from '../../../shared/api'

const filenameFromDisposition = (disposition) => {
    const utf8Match = disposition?.match(/filename\*=UTF-8''([^;]+)/i)
    if (utf8Match?.[1]) return decodeURIComponent(utf8Match[1])

    const match = disposition?.match(/filename="?([^"]+)"?/i)
    return match?.[1] || 'factura.pdf'
}

const downloadBlob = (blob, filename) => {
    const objectUrl = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = objectUrl
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(objectUrl)
}

const downloadFacturaPdf = async (id) => {
    const response = await fetch(buildApiUrl(`${endpoints.facturas}/${id}/pdf`), {
        headers: {
            Accept: 'application/pdf',
            Authorization: `Bearer ${getToken()}`,
        },
    })

    if (!response.ok) {
        let message = 'No se ha podido descargar el PDF.'
        try {
            const payload = await response.json()
            message = payload?.message || message
        } catch {
            // Binary endpoint.
        }
        throw new Error(message)
    }

    const blob = await response.blob()
    const filename = filenameFromDisposition(response.headers.get('Content-Disposition') || '')
    downloadBlob(blob, filename)
}

export const facturasService = {
    list: (params) => apiRequest(endpoints.facturas, { params }),
    get: (id) => apiRequest(`${endpoints.facturas}/${id}`),
    create: (data) => apiRequest(endpoints.facturas, { method: 'POST', body: data }),
    update: (id, data) => apiRequest(`${endpoints.facturas}/${id}`, { method: 'PUT', body: data }),
    emitir: (id) => apiRequest(`${endpoints.facturas}/${id}/emitir`, { method: 'POST' }),
    marcarPagada: (id) => apiRequest(`${endpoints.facturas}/${id}/marcar-pagada`, { method: 'POST' }),
    anular: (id, motivoAnulacion) =>
        apiRequest(`${endpoints.facturas}/${id}/anular`, {
            method: 'POST',
            body: { motivo_anulacion: motivoAnulacion },
        }),
    rectificar: (id, motivoRectificacion) =>
        apiRequest(`${endpoints.facturas}/${id}/rectificar`, {
            method: 'POST',
            body: { motivo_rectificacion: motivoRectificacion },
        }),
    downloadPdf: downloadFacturaPdf,
}

export const facturasApi = facturasService

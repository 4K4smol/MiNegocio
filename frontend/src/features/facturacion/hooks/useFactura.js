import { useCallback, useEffect, useState } from 'react'
import { unwrapApiData } from '../../../shared/utils/apiResponse'
import { facturasService } from '../services/facturasService'

export function useFactura(facturaId) {
    const [factura, setFactura] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')

    const load = useCallback(async () => {
        if (!facturaId) return
        setLoading(true)
        setError('')
        try {
            const response = await facturasService.get(facturaId)
            setFactura(unwrapApiData(response))
        } catch (e) {
            setError(e?.message || 'No se pudo cargar la factura.')
        } finally {
            setLoading(false)
        }
    }, [facturaId])

    useEffect(() => {
        load()
    }, [load])

    return { factura, loading, error, reload: load }
}

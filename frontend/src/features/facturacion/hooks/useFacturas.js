import { useCallback, useEffect, useState } from 'react'
import { unwrapApiCollection } from '../../../shared/utils/apiResponse'
import { facturasService } from '../services/facturasService'

export function useFacturas() {
    const [rawFacturas, setRawFacturas] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [filters, setFilters] = useState({ search: '', estado: '' })

    const load = useCallback(async () => {
        setLoading(true)
        setError('')
        try {
            const response = await facturasService.list({
                search: filters.search.trim() || undefined,
                estado: filters.estado || undefined,
            })
            setRawFacturas(unwrapApiCollection(response))
        } catch (e) {
            setError(e?.message || 'No se pudo cargar las facturas.')
            setRawFacturas([])
        } finally {
            setLoading(false)
        }
    }, [filters])

    useEffect(() => {
        load()
    }, [load])

    return { facturas: rawFacturas, rawFacturas, loading, error, filters, setFilters, reload: load }
}

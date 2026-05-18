import { useCallback, useEffect, useMemo, useState } from 'react'
import { unwrapApiCollection } from '../../../shared/utils/apiResponse'
import { facturasService } from '../services/facturasService'
import { getEstadoFactura } from '../utils/facturaUtils'

export function useFacturas() {
    const [rawFacturas, setRawFacturas] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [filters, setFilters] = useState({ search: '', estado: '' })

    const load = useCallback(async () => {
        setLoading(true)
        setError('')
        try {
            const response = await facturasService.list()
            setRawFacturas(unwrapApiCollection(response))
        } catch (e) {
            setError(e?.message || 'No se pudo cargar las facturas.')
            setRawFacturas([])
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => {
        load()
    }, [load])

    const facturas = useMemo(() => {
        let result = rawFacturas
        if (filters.search) {
            const q = filters.search.toLowerCase()
            result = result.filter((f) => {
                const num = `${f.serie || ''}-${f.numero || f.id || ''}`.toLowerCase()
                const cliente = (f.receptor_nombre_razon_social || f.cliente?.nombre || '').toLowerCase()
                return num.includes(q) || cliente.includes(q)
            })
        }
        if (filters.estado) {
            result = result.filter((f) => getEstadoFactura(f) === filters.estado)
        }
        return result
    }, [rawFacturas, filters])

    return { facturas, rawFacturas, loading, error, filters, setFilters, reload: load }
}

import { formatCurrency } from '../../../shared/utils/formatters'
import { getEstadoFactura } from '../utils/facturaUtils'

function KpiCard({ label, value, color }) {
    return (
        <div
            style={{
                background: '#fff',
                border: '1px solid #e5e7eb',
                borderRadius: '0.75rem',
                padding: '1.25rem 1.5rem',
                borderLeft: `4px solid ${color}`,
            }}
        >
            <p style={{ margin: 0, fontSize: '0.8125rem', color: '#6b7280', fontWeight: 500 }}>{label}</p>
            <p style={{ margin: '0.25rem 0 0', fontSize: '1.5rem', fontWeight: 700, color: '#111827' }}>{value}</p>
        </div>
    )
}

export function FacturasKPIs({ facturas = [] }) {
    const emitidas = facturas.filter((f) => {
        const estado = getEstadoFactura(f)
        return estado && estado !== 'borrador'
    })
    const borradores = facturas.filter((f) => getEstadoFactura(f) === 'borrador')
    const totalFacturado = emitidas.reduce((acc, f) => acc + (parseFloat(f.total) || 0), 0)
    const pendienteCobro = facturas
        .filter((f) => getEstadoFactura(f) === 'emitida')
        .reduce((acc, f) => acc + (parseFloat(f.total) || 0), 0)

    return (
        <div
            style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
                gap: '1rem',
                marginBottom: '1.5rem',
            }}
        >
            <KpiCard label="Facturas emitidas" value={emitidas.length} color="var(--color-primary, #6d28d9)" />
            <KpiCard label="Total facturado" value={formatCurrency(totalFacturado)} color="#059669" />
            <KpiCard label="Pendiente de cobro" value={formatCurrency(pendienteCobro)} color="#d97706" />
            <KpiCard label="Borradores" value={borradores.length} color="#6b7280" />
        </div>
    )
}

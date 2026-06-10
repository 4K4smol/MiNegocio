import { formatCurrency } from '../../../shared/utils/formatters'
import {
    isFacturaAnulada,
    isFacturaBorrador,
    isFacturaEmitida,
    isFacturaFinalizada,
    isFacturaNegativa,
    isFacturaPagada,
    isFacturaRectificativa,
} from '../utils/facturaUtils'

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
    const documentosEmitidos = facturas.filter((f) => !isFacturaBorrador(f))
    const borradores = facturas.filter((f) => isFacturaBorrador(f))
    const totalFacturado = documentosEmitidos
        .filter((f) => !isFacturaAnulada(f))
        .reduce((acc, f) => acc + (parseFloat(f.total) || 0), 0)
    const pendienteCobro = facturas
        .filter((f) =>
            !isFacturaFinalizada(f) &&
            isFacturaEmitida(f) &&
            !isFacturaPagada(f) &&
            Number(f.total || 0) > 0
        )
        .reduce((acc, f) => acc + (parseFloat(f.total) || 0), 0)
    const pendienteAbono = facturas
        .filter((f) =>
            !isFacturaAnulada(f) &&
            isFacturaEmitida(f) &&
            isFacturaRectificativa(f) &&
            isFacturaNegativa(f) &&
            !isFacturaPagada(f)
        )
        .reduce((acc, f) => acc + Math.abs(parseFloat(f.total) || 0), 0)

    return (
        <div
            style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))',
                gap: '1rem',
                marginBottom: '1.5rem',
            }}
        >
            <KpiCard label="Documentos emitidos" value={documentosEmitidos.length} color="var(--color-primary, #6d28d9)" />
            <KpiCard label="Total facturado neto" value={formatCurrency(totalFacturado)} color="#059669" />
            <KpiCard label="Pendiente de cobro" value={formatCurrency(pendienteCobro)} color="#d97706" />
            <KpiCard label="Pendiente de abono" value={formatCurrency(pendienteAbono)} color="#7c3aed" />
            <KpiCard label="Borradores" value={borradores.length} color="#6b7280" />
        </div>
    )
}

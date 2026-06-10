import { useState } from 'react'
import { Link } from 'react-router-dom'
import { AppIcon } from '../../../components/ui/AppIcon'
import { appIcons } from '../../../config/appIcons'
import { DataTable } from '../../../shared/components/DataTable'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorState } from '../../../shared/components/ErrorState'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { RowActionsMenu } from '../../../shared/components/RowActionsMenu'
import { StatusBadge } from '../../../shared/components/StatusBadge'
import { formatCurrency, formatDate } from '../../../shared/utils/formatters'
import { AnularFacturaModal } from '../components/AnularFacturaModal'
import { FacturasFilters } from '../components/FacturasFilters'
import { FacturasKPIs } from '../components/FacturasKPIs'
import { RectificarFacturaModal } from '../components/RectificarFacturaModal'
import { useFacturas } from '../hooks/useFacturas'
import { facturasService } from '../services/facturasService'
import {
    getEstadoFactura,
    getNumeroFactura,
    puedeAnularFactura,
    puedeMarcarComoPagada,
    puedeRectificarFactura,
    puedeRegistrarDevolucion,
} from '../utils/facturaUtils'

export function FacturasPage() {
    const { facturas, rawFacturas, loading, error, filters, setFilters, reload } = useFacturas()
    const [saving, setSaving] = useState(false)
    const [actionError, setActionError] = useState('')
    const [anularId, setAnularId] = useState(null)
    const [rectificarId, setRectificarId] = useState(null)

    const runAction = async (action) => {
        setSaving(true)
        setActionError('')
        try {
            await action()
            reload()
        } catch (e) {
            setActionError(e?.message || 'No se pudo completar la acción.')
        } finally {
            setSaving(false)
        }
    }

    const handleAnular = (motivo) =>
        runAction(async () => {
            await facturasService.anular(anularId, motivo)
            setAnularId(null)
        })

    const handleRectificar = (motivo) =>
        runAction(async () => {
            await facturasService.rectificar(rectificarId, motivo)
            setRectificarId(null)
        })

    const handleMarcarPagada = (id) => runAction(() => facturasService.marcarPagada(id))

    const getActions = (factura) => {
        return [
            { label: 'Ver detalle', to: `/app/facturas/${factura.id}` },
            {
                label: 'Marcar como pagada',
                disabled: saving,
                hidden: !puedeMarcarComoPagada(factura),
                onClick: () => handleMarcarPagada(factura.id),
            },
            {
                label: 'Registrar devolución',
                disabled: saving,
                hidden: !puedeRegistrarDevolucion(factura),
                onClick: () => handleMarcarPagada(factura.id),
            },
            {
                label: 'Anular',
                disabled: saving,
                hidden: !puedeAnularFactura(factura),
                variant: 'danger',
                onClick: () => setAnularId(factura.id),
            },
            {
                label: 'Rectificar',
                disabled: saving,
                hidden: !puedeRectificarFactura(factura),
                onClick: () => setRectificarId(factura.id),
            },
        ]
    }

    return (
        <section className="page">
            <PageHeader
                actions={
                    <>
                        <Link className="button button-ghost" to="/app/facturas/registros">
                            <AppIcon icon={appIcons.facturas} size={18} />
                            Registros
                        </Link>
                        <Link className="button button-ghost" to="/app/facturas/verifactu">
                            <AppIcon icon={appIcons.validar} size={18} />
                            VeriFactu
                        </Link>
                        <Link className="button" to="/app/facturas/nueva">
                            <AppIcon icon={appIcons.crear} size={18} />
                            Nueva factura
                        </Link>
                    </>
                }
                description="Consulta facturas, pagos y registros de facturación."
                title="Facturación"
            />

            {!loading ? <FacturasKPIs facturas={rawFacturas} /> : null}

            <FacturasFilters filters={filters} onChange={setFilters} />

            {loading ? <LoadingState>Cargando facturas...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {actionError ? <ErrorState>{actionError}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={['Factura', 'Cliente', 'Fecha', 'Estado', 'Total', 'Acciones']}
                    empty={
                        !facturas.length ? (
                            <EmptyState
                                description={
                                    filters.search || filters.estado
                                        ? 'No hay facturas que coincidan con los filtros aplicados.'
                                        : 'Las facturas creadas desde órdenes completadas aparecerán aquí.'
                                }
                                title="No hay facturas"
                            />
                        ) : null
                    }
                >
                    {facturas.map((factura) => (
                        <tr key={factura.id}>
                            <td>
                                <strong>{getNumeroFactura(factura)}</strong>
                                {factura.tipo_factura ? <small>{factura.tipo_factura}</small> : null}
                            </td>
                            <td>
                                {factura.receptor_nombre_razon_social ||
                                    factura.cliente?.nombre ||
                                    'Sin cliente'}
                            </td>
                            <td>{formatDate(factura.fecha_emision)}</td>
                            <td>
                                <StatusBadge status={getEstadoFactura(factura)} />
                            </td>
                            <td>{formatCurrency(factura.total)}</td>
                            <td>
                                <RowActionsMenu actions={getActions(factura)} />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}

            <AnularFacturaModal
                loading={saving}
                open={Boolean(anularId)}
                onClose={() => setAnularId(null)}
                onConfirm={handleAnular}
            />
            <RectificarFacturaModal
                loading={saving}
                open={Boolean(rectificarId)}
                onClose={() => setRectificarId(null)}
                onConfirm={handleRectificar}
            />
        </section>
    )
}

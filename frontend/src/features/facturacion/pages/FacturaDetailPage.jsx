import { useCallback, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { ConfirmModal } from '../../../shared/components/ConfirmModal'
import { DataTable } from '../../../shared/components/DataTable'
import { ErrorState } from '../../../shared/components/ErrorState'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { StatusBadge } from '../../../shared/components/StatusBadge'
import { formatCurrency, formatDate } from '../../../shared/utils/formatters'
import { AnularFacturaModal } from '../components/AnularFacturaModal'
import { RectificarFacturaModal } from '../components/RectificarFacturaModal'
import { VerifactuBadge } from '../components/VerifactuBadge'
import { useFactura } from '../hooks/useFactura'
import { facturasService } from '../services/facturasService'
import {
    getEstadoFactura,
    getNumeroFactura,
    isFacturaBorrador,
    isFacturaEmitida,
    isFacturaFinalizada,
    isFacturaPagada,
} from '../utils/facturaUtils'

const TABS = [
    { id: 'resumen', label: 'Resumen' },
    { id: 'lineas', label: 'Líneas e impuestos' },
    { id: 'tecnico', label: 'Registro técnico' },
    { id: 'verifactu', label: 'VeriFactu' },
]

function TabNav({ tabs, active, onChange }) {
    return (
        <nav style={{ display: 'flex', borderBottom: '1px solid #e5e7eb', marginBottom: '1.5rem' }}>
            {tabs.map((tab) => (
                <button
                    key={tab.id}
                    style={{
                        background: 'none',
                        border: 'none',
                        borderBottom:
                            active === tab.id
                                ? '2px solid var(--color-primary, #6d28d9)'
                                : '2px solid transparent',
                        color: active === tab.id ? 'var(--color-primary, #6d28d9)' : '#6b7280',
                        cursor: 'pointer',
                        fontWeight: active === tab.id ? 600 : 400,
                        padding: '0.75rem 1.25rem',
                        fontSize: '0.9375rem',
                        transition: 'color 0.15s',
                    }}
                    type="button"
                    onClick={() => onChange(tab.id)}
                >
                    {tab.label}
                </button>
            ))}
        </nav>
    )
}

function ResumenTab({ factura }) {
    return (
        <div className="form-grid">
            <dl className="detail-list is-wide">
                <div>
                    <dt>Número</dt>
                    <dd>{getNumeroFactura(factura)}</dd>
                </div>
                <div>
                    <dt>Tipo</dt>
                    <dd>{factura.tipo_factura || 'Factura'}</dd>
                </div>
                <div>
                    <dt>Estado</dt>
                    <dd><StatusBadge status={getEstadoFactura(factura)} /></dd>
                </div>
                <div>
                    <dt>Fecha de emisión</dt>
                    <dd>{formatDate(factura.fecha_emision)}</dd>
                </div>
                {factura.fecha_operacion ? (
                    <div>
                        <dt>Fecha de operación</dt>
                        <dd>{formatDate(factura.fecha_operacion)}</dd>
                    </div>
                ) : null}
                <div>
                    <dt>Cliente / Receptor</dt>
                    <dd>
                        {factura.receptor_nombre_razon_social ||
                            factura.cliente?.nombre ||
                            'Sin cliente'}
                    </dd>
                </div>
                {factura.receptor_nif ? (
                    <div>
                        <dt>NIF / CIF receptor</dt>
                        <dd>{factura.receptor_nif}</dd>
                    </div>
                ) : null}
                {factura.receptor_direccion ? (
                    <div>
                        <dt>Dirección receptor</dt>
                        <dd>{factura.receptor_direccion}</dd>
                    </div>
                ) : null}
                {factura.notas ? (
                    <div>
                        <dt>Notas</dt>
                        <dd>{factura.notas}</dd>
                    </div>
                ) : null}
            </dl>

            <dl className="detail-list">
                <div>
                    <dt>Base imponible</dt>
                    <dd>{formatCurrency(factura.base_imponible)}</dd>
                </div>
                <div>
                    <dt>IVA</dt>
                    <dd>{formatCurrency(factura.cuota_iva)}</dd>
                </div>
                {factura.cuota_recargo ? (
                    <div>
                        <dt>Recargo de equivalencia</dt>
                        <dd>{formatCurrency(factura.cuota_recargo)}</dd>
                    </div>
                ) : null}
                {factura.irpf ? (
                    <div>
                        <dt>IRPF</dt>
                        <dd>{formatCurrency(factura.irpf)}</dd>
                    </div>
                ) : null}
                <div>
                    <dt>Total</dt>
                    <dd><strong>{formatCurrency(factura.total)}</strong></dd>
                </div>
                <div>
                    <dt>Pagada</dt>
                    <dd>{factura.pagada ? 'Sí' : 'No'}</dd>
                </div>
            </dl>

            {factura.motivo_anulacion ? (
                <div
                    style={{
                        gridColumn: '1 / -1',
                        padding: '1rem',
                        borderRadius: '0.5rem',
                        background: '#fef2f2',
                        borderLeft: '4px solid #ef4444',
                    }}
                >
                    <strong style={{ color: '#dc2626' }}>Motivo de anulación:</strong>{' '}
                    {factura.motivo_anulacion}
                </div>
            ) : null}

            {factura.motivo_rectificacion ? (
                <div
                    style={{
                        gridColumn: '1 / -1',
                        padding: '1rem',
                        borderRadius: '0.5rem',
                        background: '#fffbeb',
                        borderLeft: '4px solid #f59e0b',
                    }}
                >
                    <strong style={{ color: '#d97706' }}>Motivo de rectificación:</strong>{' '}
                    {factura.motivo_rectificacion}
                </div>
            ) : null}
        </div>
    )
}

function LineasTab({ factura }) {
    if (!factura.lineas?.length) {
        return <p style={{ color: '#6b7280' }}>Esta factura no tiene líneas registradas.</p>
    }
    return (
        <DataTable columns={['Descripción', 'Cantidad', 'Precio unit.', 'Dto. %', 'IVA %', 'Total']}>
            {factura.lineas.map((linea) => (
                <tr key={linea.id}>
                    <td>
                        <strong>{linea.descripcion || linea.servicio_nombre_snapshot}</strong>
                    </td>
                    <td>{linea.cantidad}</td>
                    <td>{formatCurrency(linea.precio_unitario)}</td>
                    <td>{linea.descuento_porcentaje ?? 0}%</td>
                    <td>{linea.iva_porcentaje ?? 0}%</td>
                    <td>{formatCurrency(linea.total)}</td>
                </tr>
            ))}
        </DataTable>
    )
}

function TecnicoTab({ factura }) {
    const registro = factura.registro_facturacion || factura.registro || null
    if (!registro) {
        return (
            <p style={{ color: '#6b7280' }}>
                El registro técnico se genera automáticamente al emitir la factura.
            </p>
        )
    }
    return (
        <dl className="detail-list is-wide">
            {registro.id ? (
                <div><dt>ID Registro</dt><dd><code>{registro.id}</code></dd></div>
            ) : null}
            {registro.huella || registro.hash ? (
                <div>
                    <dt>Huella / Hash</dt>
                    <dd style={{ wordBreak: 'break-all', fontFamily: 'monospace', fontSize: '0.8125rem' }}>
                        {registro.huella || registro.hash}
                    </dd>
                </div>
            ) : null}
            {registro.modo_generacion ? (
                <div><dt>Modo de generación</dt><dd>{registro.modo_generacion}</dd></div>
            ) : null}
            {registro.tipo_huella ? (
                <div><dt>Tipo de huella</dt><dd>{registro.tipo_huella}</dd></div>
            ) : null}
            {registro.fecha_hora_expedicion ? (
                <div><dt>Fecha de expedición</dt><dd>{formatDate(registro.fecha_hora_expedicion)}</dd></div>
            ) : null}
            {registro.numero_registro_acuse ? (
                <div><dt>Nº acuse de recibo</dt><dd><code>{registro.numero_registro_acuse}</code></dd></div>
            ) : null}
        </dl>
    )
}

function VerifactuTab({ factura }) {
    const estado = factura.verifactu_estado || factura.estado_verifactu || null
    return (
        <dl className="detail-list">
            <div>
                <dt>Estado VeriFactu</dt>
                <dd><VerifactuBadge estado={estado || 'no_configurado'} /></dd>
            </div>
            {factura.verifactu_csv ? (
                <div><dt>CSV</dt><dd><code>{factura.verifactu_csv}</code></dd></div>
            ) : null}
            {factura.verifactu_qr ? (
                <div>
                    <dt>Código QR</dt>
                    <dd>
                        <code style={{ wordBreak: 'break-all', fontSize: '0.8125rem' }}>
                            {factura.verifactu_qr}
                        </code>
                    </dd>
                </div>
            ) : null}
            {!estado ? (
                <div style={{ gridColumn: '1 / -1' }}>
                    <p style={{ color: '#6b7280', margin: 0 }}>
                        VeriFactu no está configurado o esta factura no ha sido enviada al sistema de verificación.
                    </p>
                </div>
            ) : null}
        </dl>
    )
}

export function FacturaDetailPage() {
    const { facturaId } = useParams()
    const navigate = useNavigate()
    const { factura, loading, error, reload } = useFactura(facturaId)
    const [activeTab, setActiveTab] = useState('resumen')
    const [saving, setSaving] = useState(false)
    const [actionError, setActionError] = useState('')
    const [emitirOpen, setEmitirOpen] = useState(false)
    const [anularOpen, setAnularOpen] = useState(false)
    const [rectificarOpen, setRectificarOpen] = useState(false)

    const runAction = useCallback(
        async (action) => {
            setSaving(true)
            setActionError('')
            try {
                await action()
                await reload()
            } catch (e) {
                setActionError(e?.message || 'No se pudo completar la acción.')
            } finally {
                setSaving(false)
            }
        },
        [reload],
    )

    const handleEmitir = () =>
        runAction(async () => {
            await facturasService.emitir(facturaId)
            setEmitirOpen(false)
        })

    const handleMarcarPagada = () => runAction(() => facturasService.marcarPagada(facturaId))

    const handleAnular = (motivo) =>
        runAction(async () => {
            await facturasService.anular(facturaId, motivo)
            setAnularOpen(false)
        })

    const handleRectificar = (motivo) =>
        runAction(async () => {
            const response = await facturasService.rectificar(facturaId, motivo)
            setRectificarOpen(false)
            if (response?.data?.id) navigate(`/app/facturas/${response.data.id}`)
            else reload()
        })

    const isBorrador = factura ? isFacturaBorrador(factura) : false
    const isEmitida = factura ? isFacturaEmitida(factura) : false
    const isPagada = factura ? isFacturaPagada(factura) : false
    const isFinalizada = factura ? isFacturaFinalizada(factura) : false
    const estado = factura ? getEstadoFactura(factura) : ''

    return (
        <section className="page">
            <PageHeader
                actions={
                    factura ? (
                        <>
                            <Link className="button button-ghost" to="/app/facturas">
                                Volver
                            </Link>
                            {isBorrador ? (
                                <Link
                                    className="button button-ghost"
                                    to={`/app/facturas/${facturaId}/editar`}
                                >
                                    Editar
                                </Link>
                            ) : null}
                            {isBorrador ? (
                                <button
                                    className="button"
                                    disabled={saving}
                                    type="button"
                                    onClick={() => setEmitirOpen(true)}
                                >
                                    Emitir factura
                                </button>
                            ) : null}
                            {isEmitida && !isPagada ? (
                                <button
                                    className="button"
                                    disabled={saving}
                                    type="button"
                                    onClick={handleMarcarPagada}
                                >
                                    {saving ? 'Guardando...' : 'Marcar como pagada'}
                                </button>
                            ) : null}
                            {!isBorrador && !isFinalizada ? (
                                <button
                                    className="button button-ghost"
                                    disabled={saving}
                                    type="button"
                                    onClick={() => setRectificarOpen(true)}
                                >
                                    Rectificar
                                </button>
                            ) : null}
                            {!isBorrador && !isFinalizada ? (
                                <button
                                    className="button button-danger"
                                    disabled={saving}
                                    type="button"
                                    onClick={() => setAnularOpen(true)}
                                >
                                    Anular
                                </button>
                            ) : null}
                        </>
                    ) : (
                        <Link className="button button-ghost" to="/app/facturas">
                            Volver
                        </Link>
                    )
                }
                description={factura ? `Estado: ${estado}` : 'Detalle de factura'}
                eyebrow="Facturación"
                title={factura ? getNumeroFactura(factura) : 'Cargando...'}
            />

            {loading ? <LoadingState>Cargando factura...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {actionError ? <ErrorState>{actionError}</ErrorState> : null}

            {!loading && factura ? (
                <>
                    <TabNav active={activeTab} tabs={TABS} onChange={setActiveTab} />
                    {activeTab === 'resumen' && <ResumenTab factura={factura} />}
                    {activeTab === 'lineas' && <LineasTab factura={factura} />}
                    {activeTab === 'tecnico' && <TecnicoTab factura={factura} />}
                    {activeTab === 'verifactu' && <VerifactuTab factura={factura} />}
                </>
            ) : null}

            <ConfirmModal
                confirmLabel="Emitir factura"
                description="Una vez emitida, la factura quedará bloqueada. El sistema asignará número, serie y los datos de registro fiscal."
                loading={saving}
                open={emitirOpen}
                title="¿Confirmar emisión?"
                tone="warning"
                onCancel={() => setEmitirOpen(false)}
                onConfirm={handleEmitir}
            />
            <AnularFacturaModal
                loading={saving}
                open={anularOpen}
                onClose={() => setAnularOpen(false)}
                onConfirm={handleAnular}
            />
            <RectificarFacturaModal
                loading={saving}
                open={rectificarOpen}
                onClose={() => setRectificarOpen(false)}
                onConfirm={handleRectificar}
            />
        </section>
    )
}

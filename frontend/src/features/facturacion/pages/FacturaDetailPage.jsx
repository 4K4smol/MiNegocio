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
    isFacturaAnulada,
    isFacturaBorrador,
    isFacturaEmitida,
    isFacturaPagada,
    isFacturaRectificada,
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
                {factura.fecha_vencimiento ? (
                    <div>
                        <dt>Fecha de vencimiento</dt>
                        <dd>{formatDate(factura.fecha_vencimiento)}</dd>
                    </div>
                ) : null}
                <div>
                    <dt>Cliente / Receptor</dt>
                    <dd>
                        {factura.receptor_nombre_razon_social ||
                            factura.cliente?.razon_social ||
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
                {factura.receptor_domicilio_fiscal ? (
                    <div>
                        <dt>Domicilio fiscal receptor</dt>
                        <dd>{factura.receptor_domicilio_fiscal}</dd>
                    </div>
                ) : null}
                {factura.observaciones ? (
                    <div>
                        <dt>Observaciones</dt>
                        <dd>{factura.observaciones}</dd>
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
                {factura.total_retencion > 0 ? (
                    <div>
                        <dt>Retención</dt>
                        <dd>− {formatCurrency(factura.total_retencion)}</dd>
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

            {factura.factura_rectificada_id ? (
                <div
                    style={{
                        gridColumn: '1 / -1',
                        padding: '1rem',
                        borderRadius: '0.5rem',
                        background: '#fffbeb',
                        borderLeft: '4px solid #f59e0b',
                    }}
                >
                    <strong style={{ color: '#d97706' }}>Factura rectificativa</strong>
                    {factura.motivo_rectificacion ? (
                        <span style={{ marginLeft: '0.5rem', color: '#374151' }}>
                            — {factura.motivo_rectificacion}
                        </span>
                    ) : null}
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
        <>
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

            {factura.impuestos?.length ? (
                <div style={{ marginTop: '1.25rem' }}>
                    <h3 style={{ fontSize: '0.9375rem', fontWeight: 600, marginBottom: '0.5rem' }}>
                        Desglose de impuestos
                    </h3>
                    <DataTable columns={['Impuesto', 'Base imponible', 'Tipo %', 'Cuota']}>
                        {factura.impuestos.map((imp) => (
                            <tr key={imp.id}>
                                <td>{imp.impuesto_nombre || imp.impuesto_codigo || 'IVA'}</td>
                                <td>{formatCurrency(imp.base_imponible)}</td>
                                <td>{imp.tipo_porcentaje ?? imp.porcentaje ?? 0}%</td>
                                <td>{formatCurrency(imp.cuota)}</td>
                            </tr>
                        ))}
                    </DataTable>
                </div>
            ) : null}
        </>
    )
}

const LABEL_TIPO_REGISTRO = {
    alta: 'Alta',
    anulacion: 'Anulación',
    rectificativa: 'Rectificativa',
}

const LABEL_ESTADO_REMISION = {
    pendiente: 'Pendiente de envío',
    enviado_simulado: 'Enviado (simulado)',
    enviado: 'Enviado a AEAT',
    aceptado: 'Aceptado por AEAT',
    rechazado: 'Rechazado por AEAT',
}

function TecnicoTab({ factura }) {
    const registros = factura.registros_facturacion ?? []
    const ultimoHash = factura.ultimo_registro_facturacion_hash
    const estadoRemision = factura.estado_remision_aeat

    if (!registros.length && !ultimoHash) {
        return (
            <p style={{ color: '#6b7280' }}>
                El registro técnico se generará al emitir la factura.
            </p>
        )
    }

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            {/* Resumen del último registro */}
            {ultimoHash ? (
                <div
                    style={{
                        padding: '1rem',
                        borderRadius: '0.5rem',
                        background: 'var(--color-primary-50, #f5f3ff)',
                        border: '1px solid var(--color-primary-200, #ddd6fe)',
                    }}
                >
                    <p style={{ margin: '0 0 0.5rem', fontWeight: 600, color: 'var(--color-primary, #6d28d9)', fontSize: '0.875rem' }}>
                        Hash del último registro
                    </p>
                    <code
                        style={{
                            wordBreak: 'break-all',
                            fontSize: '0.8125rem',
                            display: 'block',
                            color: '#111827',
                        }}
                    >
                        {ultimoHash}
                    </code>
                    {estadoRemision ? (
                        <p style={{ margin: '0.5rem 0 0', fontSize: '0.8125rem', color: '#6b7280' }}>
                            Estado de remisión: <strong>{LABEL_ESTADO_REMISION[estadoRemision] ?? estadoRemision}</strong>
                        </p>
                    ) : null}
                </div>
            ) : null}

            {/* Lista de registros */}
            {registros.map((reg, idx) => (
                <div
                    key={reg.id ?? idx}
                    style={{
                        border: '1px solid #e5e7eb',
                        borderRadius: '0.5rem',
                        padding: '1rem',
                        background: '#fafafa',
                    }}
                >
                    <p
                        style={{
                            margin: '0 0 0.75rem',
                            fontWeight: 600,
                            fontSize: '0.875rem',
                            color: '#111827',
                            display: 'flex',
                            alignItems: 'center',
                            gap: '0.5rem',
                        }}
                    >
                        <span
                            style={{
                                background: reg.tipo_registro === 'alta' ? '#dcfce7' : reg.tipo_registro === 'anulacion' ? '#fee2e2' : '#fef9c3',
                                color: reg.tipo_registro === 'alta' ? '#166534' : reg.tipo_registro === 'anulacion' ? '#991b1b' : '#854d0e',
                                borderRadius: '0.25rem',
                                padding: '0.125rem 0.5rem',
                                fontSize: '0.75rem',
                                fontWeight: 700,
                                textTransform: 'uppercase',
                                letterSpacing: '0.05em',
                            }}
                        >
                            {LABEL_TIPO_REGISTRO[reg.tipo_registro] ?? reg.tipo_registro ?? 'Registro'}
                        </span>
                        {reg.numero_completo ? `· ${reg.numero_completo}` : null}
                    </p>

                    <dl className="detail-list">
                        {reg.hash_actual ? (
                            <div>
                                <dt>Hash actual</dt>
                                <dd>
                                    <code style={{ wordBreak: 'break-all', fontSize: '0.8125rem' }}>
                                        {reg.hash_actual}
                                    </code>
                                </dd>
                            </div>
                        ) : null}
                        {reg.hash_anterior ? (
                            <div>
                                <dt>Hash anterior</dt>
                                <dd>
                                    <code style={{ wordBreak: 'break-all', fontSize: '0.8125rem', color: '#6b7280' }}>
                                        {reg.hash_anterior}
                                    </code>
                                </dd>
                            </div>
                        ) : null}
                        {reg.generado_at ? (
                            <div>
                                <dt>Generado el</dt>
                                <dd>{formatDate(reg.generado_at)}</dd>
                            </div>
                        ) : null}
                        {reg.estado_remision ? (
                            <div>
                                <dt>Estado remisión</dt>
                                <dd>{LABEL_ESTADO_REMISION[reg.estado_remision] ?? reg.estado_remision}</dd>
                            </div>
                        ) : null}
                        {reg.serie || reg.numero ? (
                            <div>
                                <dt>Serie / Número</dt>
                                <dd>
                                    {reg.serie ?? '—'} / {reg.numero ?? '—'}
                                </dd>
                            </div>
                        ) : null}
                    </dl>
                </div>
            ))}
        </div>
    )
}

function VerifactuTab({ factura }) {
    const estadoRemision = factura.estado_remision_aeat
    const datosQr = factura.datos_qr

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <dl className="detail-list">
                <div>
                    <dt>Estado de remisión</dt>
                    <dd>
                        <VerifactuBadge estado={estadoRemision || 'pendiente'} />
                    </dd>
                </div>
                {datosQr?.etiqueta ? (
                    <div>
                        <dt>Modo</dt>
                        <dd>{datosQr.etiqueta}</dd>
                    </div>
                ) : null}
                {datosQr?.hash_registro ? (
                    <div>
                        <dt>Hash registro</dt>
                        <dd>
                            <code style={{ wordBreak: 'break-all', fontSize: '0.8125rem' }}>
                                {datosQr.hash_registro}
                            </code>
                        </dd>
                    </div>
                ) : null}
                {datosQr?.url_interna ? (
                    <div>
                        <dt>URL QR interna</dt>
                        <dd>
                            <code style={{ wordBreak: 'break-all', fontSize: '0.8125rem', color: '#6b7280' }}>
                                {datosQr.url_interna}
                            </code>
                        </dd>
                    </div>
                ) : null}
                {datosQr?.emisor_nif ? (
                    <div>
                        <dt>Emisor NIF</dt>
                        <dd>{datosQr.emisor_nif}</dd>
                    </div>
                ) : null}
                {datosQr?.importe_total ? (
                    <div>
                        <dt>Importe total</dt>
                        <dd>{datosQr.importe_total} EUR</dd>
                    </div>
                ) : null}
            </dl>

            {!estadoRemision ? (
                <p style={{ color: '#6b7280', margin: 0, fontSize: '0.875rem' }}>
                    El registro VeriFactu se generará al emitir la factura. La remisión real a la AEAT requiere configuración adicional.
                </p>
            ) : null}

            <p
                style={{
                    margin: 0,
                    fontSize: '0.8125rem',
                    color: '#9ca3af',
                    padding: '0.75rem',
                    borderRadius: '0.375rem',
                    background: '#f9fafb',
                    border: '1px solid #f3f4f6',
                }}
            >
                MVP: La remisión a la AEAT es simulada. Para integración real con VeriFactu es necesario configurar el servicio de envío y las credenciales de la AEAT.
            </p>
        </div>
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

    const handleDescargarPdf = () =>
        runAction(async () => {
            await facturasService.downloadPdf(facturaId)
        })

    const handleMarcarPagada = () => runAction(() => facturasService.marcarPagada(facturaId))

    const handleAnular = (motivo) =>
        runAction(async () => {
            await facturasService.anular(facturaId, motivo)
            setAnularOpen(false)
            setActiveTab('tecnico')
        })

    const handleRectificar = (motivo) =>
        runAction(async () => {
            const response = await facturasService.rectificar(facturaId, motivo)
            setRectificarOpen(false)
            if (response?.data?.id) navigate(`/app/facturas/${response.data.id}`)
            else reload()
        })

    // Cálculo de estados
    const isBorrador = factura ? isFacturaBorrador(factura) : false
    const isEmitida = factura ? isFacturaEmitida(factura) : false
    const isPagada = factura ? isFacturaPagada(factura) : false
    const isAnulada = factura ? isFacturaAnulada(factura) : false
    const isRectificada = factura ? isFacturaRectificada(factura) : false
    const isFinalizada = isAnulada || isRectificada
    const estado = factura ? getEstadoFactura(factura) : ''

    // Reglas de acciones:
    // borrador → editar + emitir
    // emitida → marcar pagada (si no pagada) + rectificar + anular
    // pagada → rectificar (MVP; el backend puede rechazarlo)
    // anulada / rectificada → sin acciones
    const puedeEditar = isBorrador
    const puedeEmitir = isBorrador
    const puedeDescargarPdf = !isBorrador
    const puedeMarcarPagada = isEmitida && !isPagada
    const puedeRectificar = !isBorrador && !isFinalizada
    const puedeAnular = (isEmitida || isPagada) && !isFinalizada

    return (
        <section className="page">
            <PageHeader
                actions={
                    factura ? (
                        <>
                            <Link className="button button-ghost" to="/app/facturas">
                                Volver
                            </Link>

                            {puedeEditar ? (
                                <Link
                                    className="button button-ghost"
                                    to={`/app/facturas/${facturaId}/editar`}
                                >
                                    Editar
                                </Link>
                            ) : null}

                            {puedeEmitir ? (
                                <button
                                    className="button"
                                    disabled={saving}
                                    type="button"
                                    onClick={() => setEmitirOpen(true)}
                                >
                                    Emitir factura
                                </button>
                            ) : null}

                            {puedeDescargarPdf ? (
                                <button
                                    className="button button-ghost"
                                    disabled={saving}
                                    type="button"
                                    onClick={handleDescargarPdf}
                                >
                                    {saving ? 'Preparando...' : 'Descargar PDF'}
                                </button>
                            ) : null}

                            {puedeMarcarPagada ? (
                                <button
                                    className="button"
                                    disabled={saving}
                                    type="button"
                                    onClick={handleMarcarPagada}
                                >
                                    {saving ? 'Guardando…' : 'Marcar como pagada'}
                                </button>
                            ) : null}

                            {puedeRectificar ? (
                                <button
                                    className="button button-ghost"
                                    disabled={saving}
                                    title={isPagada ? 'La rectificación de facturas pagadas puede requerir confirmación adicional.' : undefined}
                                    type="button"
                                    onClick={() => setRectificarOpen(true)}
                                >
                                    Crear rectificativa total
                                </button>
                            ) : null}

                            {puedeAnular ? (
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
                title={factura ? getNumeroFactura(factura) : 'Cargando…'}
            />

            {loading ? <LoadingState>Cargando factura…</LoadingState> : null}
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

import { useCallback, useEffect, useState } from 'react'
import { AppIcon } from '../../../components/ui/AppIcon'
import { appIcons } from '../../../config/appIcons'
import { DataTable } from '../../../shared/components/DataTable'
import { ErrorState } from '../../../shared/components/ErrorState'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { unwrapApiCollection, unwrapApiData } from '../../../shared/utils/apiResponse'
import { formatDate } from '../../../shared/utils/formatters'
import { VerifactuBadge } from '../components/VerifactuBadge'
import { verifactuService } from '../services/verifactuService'

function StatCard({ label, value, color }) {
    return (
        <div
            style={{
                background: '#fff',
                border: '1px solid #e5e7eb',
                borderRadius: '0.75rem',
                padding: '1rem 1.25rem',
                textAlign: 'center',
            }}
        >
            <p style={{ margin: 0, fontSize: '1.75rem', fontWeight: 700, color: color || '#111827' }}>
                {value ?? '—'}
            </p>
            <p style={{ margin: '0.25rem 0 0', fontSize: '0.8125rem', color: '#6b7280' }}>{label}</p>
        </div>
    )
}

export function VerifactuPage() {
    const [data, setData] = useState({ estado: null, config: null, incidencias: [] })
    const [loading, setLoading] = useState(true)
    const [sending, setSending] = useState(false)
    const [sendOk, setSendOk] = useState(null)
    const [sendError, setSendError] = useState('')

    const load = useCallback(async () => {
        setLoading(true)
        try {
            const [estadoResult, configResult, incidenciasResult] = await Promise.allSettled([
                verifactuService.getEstado(),
                verifactuService.getConfiguracion(),
                verifactuService.getIncidencias(),
            ])
            setData({
                estado: estadoResult.status === 'fulfilled' ? unwrapApiData(estadoResult.value) : null,
                config: configResult.status === 'fulfilled' ? unwrapApiData(configResult.value) : null,
                incidencias:
                    incidenciasResult.status === 'fulfilled'
                        ? unwrapApiCollection(incidenciasResult.value)
                        : [],
            })
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => {
        load()
    }, [load])

    const handleEnviarPendientes = async () => {
        setSending(true)
        setSendOk(null)
        setSendError('')
        try {
            const result = await verifactuService.enviarPendientes()
            setSendOk(result?.data || { enviados: 0 })
            load()
        } catch (e) {
            setSendError(e?.message || 'Error al enviar registros pendientes.')
        } finally {
            setSending(false)
        }
    }

    const { estado, config, incidencias } = data
    const stats = estado?.estadisticas || estado?.stats || {}

    return (
        <section className="page">
            <PageHeader
                actions={
                    <button
                        className="button"
                        disabled={sending}
                        type="button"
                        onClick={handleEnviarPendientes}
                    >
                        <AppIcon icon={appIcons.validar} size={18} />
                        {sending ? 'Enviando...' : 'Enviar pendientes'}
                    </button>
                }
                description="Estado del sistema de verificación de facturas según RD 1007/2023."
                eyebrow="Facturación"
                title="VeriFactu"
            />

            {loading ? <LoadingState>Cargando estado VeriFactu...</LoadingState> : null}

            {sendOk ? (
                <div
                    style={{
                        background: '#f0fdf4',
                        border: '1px solid #bbf7d0',
                        borderRadius: '0.5rem',
                        padding: '1rem 1.25rem',
                        marginBottom: '1rem',
                        color: '#15803d',
                    }}
                >
                    Registros enviados correctamente.
                    {sendOk.enviados !== undefined ? ` Enviados: ${sendOk.enviados}.` : ''}
                </div>
            ) : null}
            {sendError ? <ErrorState>{sendError}</ErrorState> : null}

            {!loading ? (
                <>
                    <div
                        style={{
                            background: '#fff',
                            border: '1px solid #e5e7eb',
                            borderRadius: '0.75rem',
                            padding: '1.25rem 1.5rem',
                            marginBottom: '1.5rem',
                            display: 'flex',
                            alignItems: 'center',
                            gap: '1.5rem',
                            flexWrap: 'wrap',
                        }}
                    >
                        <div>
                            <p style={{ margin: 0, fontSize: '0.8125rem', color: '#6b7280', fontWeight: 500 }}>
                                Estado del sistema
                            </p>
                            <div style={{ marginTop: '0.375rem' }}>
                                <VerifactuBadge estado={estado?.estado || 'no_configurado'} />
                            </div>
                        </div>
                        {estado?.ultimo_envio ? (
                            <p style={{ margin: 0, marginLeft: 'auto', fontSize: '0.875rem', color: '#6b7280' }}>
                                Último envío: {formatDate(estado.ultimo_envio)}
                            </p>
                        ) : null}
                    </div>

                    <div
                        style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(auto-fit, minmax(140px, 1fr))',
                            gap: '1rem',
                            marginBottom: '1.5rem',
                        }}
                    >
                        <StatCard label="Enviados" value={stats.enviados ?? estado?.enviados} color="#6d28d9" />
                        <StatCard label="Aceptados" value={stats.aceptados ?? estado?.aceptados} color="#059669" />
                        <StatCard label="Pendientes" value={stats.pendientes ?? estado?.pendientes} color="#d97706" />
                        <StatCard label="Rechazados" value={stats.rechazados ?? estado?.rechazados} color="#dc2626" />
                        <StatCard label="Errores técnicos" value={stats.errores ?? estado?.errores} color="#9333ea" />
                    </div>

                    {config ? (
                        <div style={{ marginBottom: '1.5rem' }}>
                            <h2 style={{ fontSize: '1rem', fontWeight: 600, marginBottom: '0.75rem' }}>
                                Configuración
                            </h2>
                            <dl className="detail-list">
                                {config.nif_titular ? (
                                    <div><dt>NIF titular</dt><dd>{config.nif_titular}</dd></div>
                                ) : null}
                                {config.nombre_razon_social ? (
                                    <div><dt>Razón social</dt><dd>{config.nombre_razon_social}</dd></div>
                                ) : null}
                                {config.url_sistema ? (
                                    <div><dt>URL sistema</dt><dd><code>{config.url_sistema}</code></dd></div>
                                ) : null}
                                {config.modo ? (
                                    <div><dt>Modo</dt><dd>{config.modo}</dd></div>
                                ) : null}
                                {config.activo !== undefined ? (
                                    <div><dt>Activo</dt><dd>{config.activo ? 'Sí' : 'No'}</dd></div>
                                ) : null}
                            </dl>
                        </div>
                    ) : (
                        <div
                            style={{
                                background: '#fafafa',
                                border: '1px solid #e5e7eb',
                                borderRadius: '0.5rem',
                                padding: '1rem 1.25rem',
                                marginBottom: '1.5rem',
                                color: '#6b7280',
                            }}
                        >
                            VeriFactu no está configurado en este sistema. Configúralo desde Configuración
                            o contacta con soporte técnico.
                        </div>
                    )}

                    {incidencias.length > 0 ? (
                        <div style={{ marginBottom: '1.5rem' }}>
                            <h2 style={{ fontSize: '1rem', fontWeight: 600, marginBottom: '0.75rem' }}>
                                Incidencias recientes
                            </h2>
                            <DataTable columns={['Fecha', 'Tipo', 'Descripción', 'Estado']}>
                                {incidencias.map((inc, i) => (
                                    <tr key={inc.id || i}>
                                        <td>{formatDate(inc.fecha || inc.created_at)}</td>
                                        <td>{inc.tipo || '—'}</td>
                                        <td>{inc.descripcion || inc.mensaje || '—'}</td>
                                        <td><VerifactuBadge estado={inc.estado || 'error_tecnico'} /></td>
                                    </tr>
                                ))}
                            </DataTable>
                        </div>
                    ) : null}

                    <div
                        style={{
                            background: '#f9fafb',
                            border: '1px solid #e5e7eb',
                            borderRadius: '0.75rem',
                            padding: '1.25rem 1.5rem',
                        }}
                    >
                        <h3 style={{ fontSize: '0.9rem', fontWeight: 600, margin: '0 0 0.5rem' }}>
                            Declaración responsable
                        </h3>
                        <p style={{ margin: 0, fontSize: '0.875rem', color: '#6b7280', lineHeight: 1.6 }}>
                            Este sistema de facturación cumple con los requisitos técnicos establecidos en el
                            Real Decreto 1007/2023, de 5 de diciembre, por el que se aprueba el Reglamento
                            que establece los requisitos que deben adoptar los sistemas y programas
                            informáticos o electrónicos que soporten los procesos de facturación de
                            empresarios y profesionales.
                        </p>
                    </div>
                </>
            ) : null}
        </section>
    )
}

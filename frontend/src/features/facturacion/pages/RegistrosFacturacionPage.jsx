import { useState } from 'react'
import { Link } from 'react-router-dom'
import { AppIcon } from '../../../components/ui/AppIcon'
import { appIcons } from '../../../config/appIcons'
import { DataTable } from '../../../shared/components/DataTable'
import { EmptyState } from '../../../shared/components/EmptyState'
import { ErrorState } from '../../../shared/components/ErrorState'
import { LoadingState } from '../../../shared/components/LoadingState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { useResourceList } from '../../../shared/hooks/useResourceList'
import { unwrapApiData } from '../../../shared/utils/apiResponse'
import { formatDate } from '../../../shared/utils/formatters'
import { registrosFacturacionService } from '../services/registrosFacturacionService'

export function RegistrosFacturacionPage() {
    const { error, items: registros, loading } = useResourceList(registrosFacturacionService.list)
    const [validating, setValidating] = useState(false)
    const [validationResult, setValidationResult] = useState(null)
    const [validationError, setValidationError] = useState('')

    const handleValidarCadena = async () => {
        setValidating(true)
        setValidationResult(null)
        setValidationError('')
        try {
            const response = await registrosFacturacionService.validarCadena()
            setValidationResult(unwrapApiData(response))
        } catch (e) {
            setValidationError(e?.message || 'Error al validar la cadena de integridad.')
        } finally {
            setValidating(false)
        }
    }

    return (
        <section className="page">
            <PageHeader
                actions={
                    <>
                        <Link className="button button-ghost" to="/app/facturas">
                            <AppIcon icon={appIcons.facturas} size={18} />
                            Facturas
                        </Link>
                        <button
                            className="button button-ghost"
                            disabled={validating}
                            type="button"
                            onClick={handleValidarCadena}
                        >
                            <AppIcon icon={appIcons.validar} size={18} />
                            {validating ? 'Validando...' : 'Validar cadena'}
                        </button>
                    </>
                }
                description="Registro tecnico e inmutable de altas y anulaciones de facturacion. Solo lectura."
                eyebrow="Facturacion"
                title="Registros de facturacion"
            />

            <div
                style={{
                    background: '#f0f9ff',
                    border: '1px solid #bae6fd',
                    borderRadius: '0.5rem',
                    padding: '1rem 1.25rem',
                    marginBottom: '1.5rem',
                    color: '#0369a1',
                    fontSize: '0.9rem',
                }}
            >
                Los registros se generan automaticamente al emitir o anular facturas. Validar la cadena no modifica datos
                ni envia nada a AEAT: solo recalcula hashes y comprueba el encadenamiento interno.
            </div>

            {validationResult?.valida && validationResult.total_registros > 0 ? (
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
                    Cadena de integridad validada correctamente. Registros comprobados: {validationResult.total_registros}.
                </div>
            ) : null}
            {validationResult?.valida && validationResult.total_registros === 0 ? (
                <div
                    style={{
                        background: '#f9fafb',
                        border: '1px solid #e5e7eb',
                        borderRadius: '0.5rem',
                        padding: '1rem 1.25rem',
                        marginBottom: '1rem',
                        color: '#6b7280',
                    }}
                >
                    No hay registros de facturación que validar todavía. Los registros se generan al emitir o anular facturas.
                </div>
            ) : null}
            {validationResult && !validationResult.valida ? (
                <div
                    style={{
                        background: '#fef2f2',
                        border: '1px solid #fecaca',
                        borderRadius: '0.5rem',
                        padding: '1rem 1.25rem',
                        marginBottom: '1rem',
                        color: '#991b1b',
                    }}
                >
                    <strong>La cadena de integridad tiene incidencias.</strong>
                    <ul style={{ margin: '0.75rem 0 0', paddingLeft: '1.25rem' }}>
                        {(validationResult.errores || []).map((currentError) => (
                            <li key={`${currentError.registro_id}-${currentError.motivo}`}>
                                Registro {currentError.registro_id}: {currentError.motivo}
                            </li>
                        ))}
                    </ul>
                </div>
            ) : null}
            {validationError ? <ErrorState>{validationError}</ErrorState> : null}

            {loading ? <LoadingState>Cargando registros...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={['ID', 'Factura', 'Registro', 'Hash', 'Fecha expedicion', 'Estado remision']}
                    empty={
                        !registros.length ? (
                            <EmptyState
                                description="Los registros apareceran aqui cuando se emitan o anulen facturas."
                                title="Sin registros"
                            />
                        ) : null
                    }
                >
                    {registros.map((registro) => (
                        <tr key={registro.id}>
                            <td>
                                <code style={{ fontSize: '0.8125rem' }}>{registro.id}</code>
                            </td>
                            <td>
                                {registro.factura_id ? (
                                    <Link to={`/app/facturas/${registro.factura_id}`}>
                                        {registro.numero_completo || [registro.serie, registro.numero].filter(Boolean).join('-') || registro.factura_id}
                                    </Link>
                                ) : '—'}
                            </td>
                            <td>{registro.tipo_registro || '—'}</td>
                            <td>
                                <code style={{ fontFamily: 'monospace', fontSize: '0.8125rem' }}>
                                    {registro.hash_actual ? `${registro.hash_actual.substring(0, 16)}...` : '—'}
                                </code>
                            </td>
                            <td>{formatDate(registro.fecha_expedicion || registro.generado_at)}</td>
                            <td>{registro.estado_remision || 'pendiente'}</td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}
        </section>
    )
}

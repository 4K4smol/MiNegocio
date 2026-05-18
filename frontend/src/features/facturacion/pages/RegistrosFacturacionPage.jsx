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
import { formatDate } from '../../../shared/utils/formatters'
import { registrosFacturacionService } from '../services/registrosFacturacionService'

export function RegistrosFacturacionPage() {
    const { error, items: registros, loading } = useResourceList(registrosFacturacionService.list)
    const [validating, setValidating] = useState(false)
    const [validationOk, setValidationOk] = useState(false)
    const [validationError, setValidationError] = useState('')

    const handleValidarCadena = async () => {
        setValidating(true)
        setValidationOk(false)
        setValidationError('')
        try {
            await registrosFacturacionService.validarCadena()
            setValidationOk(true)
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
                description="Registro inmutable de todas las facturas según RD 1007/2023. Solo lectura."
                eyebrow="Facturación"
                title="Registros de facturación"
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
                Los registros de facturación son datos técnicos generados automáticamente al emitir cada
                factura. Contienen la huella de integridad, firma electrónica y datos de remisión al sistema
                VeriFactu conforme al Real Decreto 1007/2023. No es posible crear ni modificar registros
                manualmente.
            </div>

            {validationOk ? (
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
                    Cadena de integridad validada correctamente.
                </div>
            ) : null}
            {validationError ? <ErrorState>{validationError}</ErrorState> : null}

            {loading ? <LoadingState>Cargando registros...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={['ID', 'Factura', 'Tipo', 'Referencia', 'Fecha expedición', 'Modo remisión']}
                    empty={
                        !registros.length ? (
                            <EmptyState
                                description="Los registros aparecerán aquí cuando se emitan facturas."
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
                                        {registro.serie_factura ? `${registro.serie_factura}-` : ''}
                                        {registro.numero_factura || registro.factura_id}
                                    </Link>
                                ) : (
                                    '—'
                                )}
                            </td>
                            <td>{registro.tipo_factura || '—'}</td>
                            <td>
                                <code style={{ fontFamily: 'monospace', fontSize: '0.8125rem' }}>
                                    {registro.huella
                                        ? `${registro.huella.substring(0, 16)}…`
                                        : registro.numero_registro_acuse || '—'}
                                </code>
                            </td>
                            <td>{formatDate(registro.fecha_hora_expedicion || registro.created_at)}</td>
                            <td>{registro.modo_remision || registro.modo_generacion || '—'}</td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}
        </section>
    )
}

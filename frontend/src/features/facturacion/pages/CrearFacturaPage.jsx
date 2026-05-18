import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AppIcon } from '../../../components/ui/AppIcon'
import { appIcons } from '../../../config/appIcons'
import { ErrorState } from '../../../shared/components/ErrorState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { unwrapApiData } from '../../../shared/utils/apiResponse'
import { clientesService } from '../../clientes/services/clientesService'
import { facturasService } from '../services/facturasService'

const defaultLinea = () => ({ descripcion: '', cantidad: 1, precio_unitario: '', iva_porcentaje: 21 })

export function CrearFacturaPage() {
    const navigate = useNavigate()
    const [saving, setSaving] = useState(false)
    const [error, setError] = useState('')

    // Clientes
    const [clientes, setClientes] = useState([])
    const [loadingClientes, setLoadingClientes] = useState(true)
    const [clienteSeleccionado, setClienteSeleccionado] = useState(null)

    const [form, setForm] = useState({
        cliente_id: '',
        fecha_emision: new Date().toISOString().slice(0, 10),
        fecha_operacion: '',
        fecha_vencimiento: '',
        observaciones: '',
    })
    const [lineas, setLineas] = useState([defaultLinea()])

    useEffect(() => {
        setLoadingClientes(true)
        clientesService
            .list({ per_page: 200 })
            .then((res) => {
                const data = res?.data?.data ?? res?.data ?? []
                setClientes(Array.isArray(data) ? data : [])
            })
            .catch(() => setClientes([]))
            .finally(() => setLoadingClientes(false))
    }, [])

    const setField = (field, value) => setForm((prev) => ({ ...prev, [field]: value }))

    const handleClienteChange = (e) => {
        const id = e.target.value
        setField('cliente_id', id)
        const cliente = clientes.find((c) => String(c.id) === String(id)) ?? null
        setClienteSeleccionado(cliente)
    }

    const setLinea = (index, field, value) =>
        setLineas((prev) => {
            const next = [...prev]
            next[index] = { ...next[index], [field]: value }
            return next
        })

    const addLinea = () => setLineas((prev) => [...prev, defaultLinea()])
    const removeLinea = (index) => setLineas((prev) => prev.filter((_, i) => i !== index))

    const handleSubmit = async (e) => {
        e.preventDefault()
        setSaving(true)
        setError('')
        try {
            const payload = {
                cliente_id: parseInt(form.cliente_id, 10),
                fecha_emision: form.fecha_emision,
                ...(form.fecha_operacion ? { fecha_operacion: form.fecha_operacion } : {}),
                ...(form.fecha_vencimiento ? { fecha_vencimiento: form.fecha_vencimiento } : {}),
                ...(form.observaciones ? { observaciones: form.observaciones } : {}),
                lineas: lineas.map((l) => ({
                    descripcion: l.descripcion,
                    cantidad: parseFloat(l.cantidad) || 1,
                    precio_unitario: parseFloat(l.precio_unitario) || 0,
                    iva_porcentaje: parseFloat(l.iva_porcentaje) || 21,
                })),
            }
            const response = await facturasService.create(payload)
            const factura = unwrapApiData(response)
            navigate(`/app/facturas/${factura.id}`)
        } catch (e) {
            setError(e?.message || 'No se pudo crear la factura.')
        } finally {
            setSaving(false)
        }
    }

    const clienteNombre = clienteSeleccionado
        ? clienteSeleccionado.razon_social || clienteSeleccionado.nombre_completo || clienteSeleccionado.nombre
        : null

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button button-ghost" to="/app/facturas">
                        Cancelar
                    </Link>
                }
                description="La factura se guardará como borrador hasta que decidas emitirla."
                eyebrow="Facturación"
                title="Nueva factura"
            />

            {error ? <ErrorState>{error}</ErrorState> : null}

            <form onSubmit={handleSubmit}>
                <div className="form-grid">
                    {/* Selector de cliente */}
                    <fieldset style={{ border: 'none', padding: 0, margin: 0 }}>
                        <legend style={{ fontWeight: 600, fontSize: '0.9375rem', marginBottom: '1rem' }}>
                            Cliente
                        </legend>
                        <div className="field">
                            <label htmlFor="cliente-select">Cliente *</label>
                            {loadingClientes ? (
                                <p style={{ color: '#6b7280', fontSize: '0.875rem' }}>Cargando clientes…</p>
                            ) : (
                                <select
                                    className="input"
                                    id="cliente-select"
                                    required
                                    value={form.cliente_id}
                                    onChange={handleClienteChange}
                                >
                                    <option value="">— Selecciona un cliente —</option>
                                    {clientes.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.razon_social || c.nombre_completo || c.nombre}
                                            {c.dni_cif ? ` (${c.dni_cif})` : ''}
                                        </option>
                                    ))}
                                </select>
                            )}
                        </div>

                        {/* Datos fiscales del cliente seleccionado — solo informativo */}
                        {clienteSeleccionado ? (
                            <div
                                style={{
                                    marginTop: '0.75rem',
                                    padding: '0.875rem 1rem',
                                    borderRadius: '0.5rem',
                                    background: 'var(--color-primary-50, #f5f3ff)',
                                    border: '1px solid var(--color-primary-200, #ddd6fe)',
                                    fontSize: '0.875rem',
                                    color: '#374151',
                                }}
                            >
                                <p style={{ margin: '0 0 0.25rem', fontWeight: 600, color: 'var(--color-primary, #6d28d9)' }}>
                                    Datos fiscales del receptor
                                </p>
                                <p style={{ margin: 0 }}>
                                    <strong>Nombre:</strong> {clienteNombre ?? '—'}
                                </p>
                                {clienteSeleccionado.dni_cif ? (
                                    <p style={{ margin: '0.125rem 0 0' }}>
                                        <strong>NIF / CIF:</strong> {clienteSeleccionado.dni_cif}
                                    </p>
                                ) : null}
                                {clienteSeleccionado.email ? (
                                    <p style={{ margin: '0.125rem 0 0' }}>
                                        <strong>Email:</strong> {clienteSeleccionado.email}
                                    </p>
                                ) : null}
                                <p style={{ margin: '0.5rem 0 0', fontSize: '0.8125rem', color: '#6b7280' }}>
                                    Los datos fiscales se completarán automáticamente desde la ficha del cliente al crear la factura.
                                </p>
                            </div>
                        ) : null}
                    </fieldset>

                    {/* Datos de la factura */}
                    <fieldset style={{ border: 'none', padding: 0, margin: 0 }}>
                        <legend style={{ fontWeight: 600, fontSize: '0.9375rem', marginBottom: '1rem' }}>
                            Datos de la factura
                        </legend>
                        <div className="field">
                            <label htmlFor="fecha-emision">Fecha de emisión *</label>
                            <input
                                className="input"
                                id="fecha-emision"
                                required
                                type="date"
                                value={form.fecha_emision}
                                onChange={(e) => setField('fecha_emision', e.target.value)}
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="fecha-operacion">Fecha de operación</label>
                            <input
                                className="input"
                                id="fecha-operacion"
                                type="date"
                                value={form.fecha_operacion}
                                onChange={(e) => setField('fecha_operacion', e.target.value)}
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="fecha-vencimiento">Fecha de vencimiento</label>
                            <input
                                className="input"
                                id="fecha-vencimiento"
                                type="date"
                                value={form.fecha_vencimiento}
                                onChange={(e) => setField('fecha_vencimiento', e.target.value)}
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="observaciones">Observaciones</label>
                            <textarea
                                className="input"
                                id="observaciones"
                                placeholder="Observaciones opcionales para esta factura…"
                                rows={3}
                                value={form.observaciones}
                                onChange={(e) => setField('observaciones', e.target.value)}
                            />
                        </div>
                    </fieldset>
                </div>

                {/* Líneas */}
                <div style={{ marginTop: '1.5rem' }}>
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            marginBottom: '1rem',
                        }}
                    >
                        <h2 style={{ fontSize: '1rem', fontWeight: 600, margin: 0 }}>Líneas</h2>
                        <button className="button button-ghost" type="button" onClick={addLinea}>
                            <AppIcon icon={appIcons.crear} size={16} />
                            Añadir línea
                        </button>
                    </div>

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                        {lineas.map((linea, i) => (
                            <div
                                key={i}
                                style={{
                                    display: 'grid',
                                    gridTemplateColumns: '1fr 80px 130px 80px auto',
                                    gap: '0.5rem',
                                    alignItems: 'end',
                                }}
                            >
                                <div className="field" style={{ margin: 0 }}>
                                    {i === 0 ? <label>Descripción</label> : null}
                                    <input
                                        className="input"
                                        placeholder="Descripción del servicio o producto"
                                        required
                                        type="text"
                                        value={linea.descripcion}
                                        onChange={(e) => setLinea(i, 'descripcion', e.target.value)}
                                    />
                                </div>
                                <div className="field" style={{ margin: 0 }}>
                                    {i === 0 ? <label>Cant.</label> : null}
                                    <input
                                        className="input"
                                        min="0"
                                        required
                                        step="0.01"
                                        type="number"
                                        value={linea.cantidad}
                                        onChange={(e) => setLinea(i, 'cantidad', e.target.value)}
                                    />
                                </div>
                                <div className="field" style={{ margin: 0 }}>
                                    {i === 0 ? <label>Precio unitario</label> : null}
                                    <input
                                        className="input"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                        step="0.01"
                                        type="number"
                                        value={linea.precio_unitario}
                                        onChange={(e) => setLinea(i, 'precio_unitario', e.target.value)}
                                    />
                                </div>
                                <div className="field" style={{ margin: 0 }}>
                                    {i === 0 ? <label>IVA %</label> : null}
                                    <input
                                        className="input"
                                        max="100"
                                        min="0"
                                        step="1"
                                        type="number"
                                        value={linea.iva_porcentaje}
                                        onChange={(e) => setLinea(i, 'iva_porcentaje', e.target.value)}
                                    />
                                </div>
                                <button
                                    className="button button-ghost"
                                    disabled={lineas.length === 1}
                                    style={{ color: '#dc2626' }}
                                    type="button"
                                    onClick={() => removeLinea(i)}
                                >
                                    <AppIcon icon={appIcons.eliminar} size={16} />
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                <div style={{ marginTop: '2rem', display: 'flex', gap: '0.75rem', justifyContent: 'flex-end' }}>
                    <Link className="button button-ghost" to="/app/facturas">
                        Cancelar
                    </Link>
                    <button className="button" disabled={saving || !form.cliente_id} type="submit">
                        {saving ? 'Guardando…' : 'Guardar borrador'}
                    </button>
                </div>
            </form>
        </section>
    )
}

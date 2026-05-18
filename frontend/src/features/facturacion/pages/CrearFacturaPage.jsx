import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AppIcon } from '../../../components/ui/AppIcon'
import { appIcons } from '../../../config/appIcons'
import { ErrorState } from '../../../shared/components/ErrorState'
import { PageHeader } from '../../../shared/components/PageHeader'
import { unwrapApiData } from '../../../shared/utils/apiResponse'
import { facturasService } from '../services/facturasService'

const defaultLinea = () => ({ descripcion: '', cantidad: 1, precio_unitario: '', iva_porcentaje: 21 })

export function CrearFacturaPage() {
    const navigate = useNavigate()
    const [saving, setSaving] = useState(false)
    const [error, setError] = useState('')
    const [form, setForm] = useState({
        receptor_nombre_razon_social: '',
        receptor_nif: '',
        receptor_direccion: '',
        fecha_emision: new Date().toISOString().slice(0, 10),
        notas: '',
    })
    const [lineas, setLineas] = useState([defaultLinea()])

    const setField = (field, value) => setForm((prev) => ({ ...prev, [field]: value }))

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
                ...form,
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
                    <fieldset style={{ border: 'none', padding: 0, margin: 0 }}>
                        <legend style={{ fontWeight: 600, fontSize: '0.9375rem', marginBottom: '1rem' }}>
                            Datos del receptor
                        </legend>
                        <div className="field">
                            <label htmlFor="receptor-nombre">Nombre / Razón social</label>
                            <input
                                className="input"
                                id="receptor-nombre"
                                placeholder="Nombre del cliente o empresa"
                                required
                                type="text"
                                value={form.receptor_nombre_razon_social}
                                onChange={(e) => setField('receptor_nombre_razon_social', e.target.value)}
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="receptor-nif">NIF / CIF</label>
                            <input
                                className="input"
                                id="receptor-nif"
                                placeholder="12345678A"
                                type="text"
                                value={form.receptor_nif}
                                onChange={(e) => setField('receptor_nif', e.target.value)}
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="receptor-direccion">Dirección</label>
                            <input
                                className="input"
                                id="receptor-direccion"
                                placeholder="Calle, número, ciudad..."
                                type="text"
                                value={form.receptor_direccion}
                                onChange={(e) => setField('receptor_direccion', e.target.value)}
                            />
                        </div>
                    </fieldset>

                    <fieldset style={{ border: 'none', padding: 0, margin: 0 }}>
                        <legend style={{ fontWeight: 600, fontSize: '0.9375rem', marginBottom: '1rem' }}>
                            Datos de la factura
                        </legend>
                        <div className="field">
                            <label htmlFor="fecha-emision">Fecha de emisión</label>
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
                            <label htmlFor="notas">Notas</label>
                            <textarea
                                className="input"
                                id="notas"
                                placeholder="Notas opcionales..."
                                rows={3}
                                value={form.notas}
                                onChange={(e) => setField('notas', e.target.value)}
                            />
                        </div>
                    </fieldset>
                </div>

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
                    <button className="button" disabled={saving} type="submit">
                        {saving ? 'Guardando...' : 'Guardar borrador'}
                    </button>
                </div>
            </form>
        </section>
    )
}

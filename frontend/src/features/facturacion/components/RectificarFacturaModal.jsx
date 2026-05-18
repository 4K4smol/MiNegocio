import { useState } from 'react'
import { Modal } from '../../../shared/components/ui/Modal'

export function RectificarFacturaModal({ open, onClose, onConfirm, loading }) {
    const [motivo, setMotivo] = useState('')

    const handleClose = () => {
        if (loading) return
        setMotivo('')
        onClose()
    }

    const handleConfirm = () => {
        if (!motivo.trim()) return
        onConfirm(motivo.trim())
    }

    return (
        <Modal
            open={open}
            title="Rectificar factura"
            subtitle="Se generará una nueva factura rectificativa"
            onClose={handleClose}
            closeDisabled={loading}
            footer={
                <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
                    <button className="button button-ghost" disabled={loading} type="button" onClick={handleClose}>
                        Cancelar
                    </button>
                    <button
                        className="button"
                        disabled={loading || !motivo.trim()}
                        type="button"
                        onClick={handleConfirm}
                    >
                        {loading ? 'Rectificando...' : 'Crear rectificativa'}
                    </button>
                </div>
            }
        >
            <div className="field">
                <label htmlFor="motivo-rectificacion">
                    Motivo de rectificación <span aria-hidden="true">*</span>
                </label>
                <textarea
                    className="input"
                    disabled={loading}
                    id="motivo-rectificacion"
                    placeholder="Indica el motivo de rectificación..."
                    rows={3}
                    value={motivo}
                    onChange={(e) => setMotivo(e.target.value)}
                />
                <small className="field-hint">
                    La factura rectificativa quedará vinculada a la original.
                </small>
            </div>
        </Modal>
    )
}

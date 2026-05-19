import { useState } from 'react'
import { Modal } from '../../../shared/components/ui/Modal'

export function AnularFacturaModal({ open, onClose, onConfirm, loading }) {
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
            title="Anular factura"
            subtitle="Esta acción no se puede deshacer. Se generará un registro técnico de anulación encadenado e irreversible en el sistema de facturación."
            onClose={handleClose}
            closeDisabled={loading}
            footer={
                <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
                    <button className="button button-ghost" disabled={loading} type="button" onClick={handleClose}>
                        Cancelar
                    </button>
                    <button
                        className="button button-danger"
                        disabled={loading || !motivo.trim()}
                        type="button"
                        onClick={handleConfirm}
                    >
                        {loading ? 'Anulando...' : 'Anular factura'}
                    </button>
                </div>
            }
        >
            <div className="field">
                <label htmlFor="motivo-anulacion">
                    Motivo de anulación <span aria-hidden="true">*</span>
                </label>
                <textarea
                    className="input"
                    disabled={loading}
                    id="motivo-anulacion"
                    placeholder="Indica el motivo de anulación..."
                    rows={3}
                    value={motivo}
                    onChange={(e) => setMotivo(e.target.value)}
                />
            </div>
        </Modal>
    )
}

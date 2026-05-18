import { Modal } from "../../../shared/components/ui/Modal";
import { formatCurrency } from "../../../shared/utils/formatters";

export function GenerarFacturaDesdeOrdenModal({ error, loading, onClose, onConfirm, open, orden }) {
    return (
        <Modal
            closeDisabled={loading}
            footer={(
                <>
                    <button className="button button-ghost" disabled={loading} type="button" onClick={onClose}>
                        Cancelar
                    </button>
                    <button className="button" disabled={loading} type="button" onClick={onConfirm}>
                        {loading ? "Generando..." : "Generar factura"}
                    </button>
                </>
            )}
            open={open}
            size="md"
            subtitle={orden?.numero}
            title="Generar factura"
            onClose={onClose}
        >
            {error ? <div className="form-error">{error}</div> : null}
            <p>
                Se generara una factura a partir de las lineas facturables pendientes de esta orden completada.
            </p>
            <dl className="detail-list">
                <div>
                    <dt>Cliente</dt>
                    <dd>{orden?.cliente?.razon_social || orden?.cliente?.nombre || "Sin cliente"}</dd>
                </div>
                <div>
                    <dt>Total estimado</dt>
                    <dd>{formatCurrency(orden?.totales?.total)}</dd>
                </div>
            </dl>
        </Modal>
    );
}


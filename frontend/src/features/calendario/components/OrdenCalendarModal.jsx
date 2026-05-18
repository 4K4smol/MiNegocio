import { Link } from "react-router-dom";
import { Modal } from "../../../shared/components/ui/Modal";
import { formatDate } from "../../../shared/utils/formatters";

export function OrdenCalendarModal({ event, onClose, open }) {
    return (
        <Modal
            footer={(
                <>
                    <button className="button button-ghost" type="button" onClick={onClose}>Cerrar</button>
                    {event?.id ? <Link className="button" to={`/app/ordenes-trabajo/${event.id}`}>Ver orden</Link> : null}
                </>
            )}
            open={open}
            size="md"
            subtitle={event?.status || "programada"}
            title={event?.title || "Orden programada"}
            onClose={onClose}
        >
            <dl className="detail-list">
                <div>
                    <dt>Cliente</dt>
                    <dd>{event?.client || "Sin cliente"}</dd>
                </div>
                <div>
                    <dt>Fecha</dt>
                    <dd>{formatDate(event?.start || event?.dateKey)}</dd>
                </div>
            </dl>
        </Modal>
    );
}


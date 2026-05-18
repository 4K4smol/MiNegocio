import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";

export function OrdenActionsDropdown({ onCancelar, onCompletar, onFacturar, orden }) {
    const estado = orden.estado || "pendiente";
    const canComplete = !["completada", "cancelada", "facturada"].includes(estado);
    const canCancel = !["cancelada", "facturada"].includes(estado);

    return (
        <RowActionsMenu
            actions={[
                { label: "Ver detalle", to: `/app/ordenes-trabajo/${orden.id}` },
                { label: "Editar", disabled: !canComplete, to: `/app/ordenes-trabajo/${orden.id}/editar` },
                { label: "Completar", disabled: !canComplete, onClick: () => onCompletar?.(orden) },
                {
                    label: "Cancelar",
                    disabled: !canCancel,
                    variant: "warning",
                    confirm: true,
                    confirmMessage: "Quieres cancelar esta orden?",
                    onClick: () => onCancelar?.(orden),
                },
                {
                    label: "Generar factura",
                    disabled: !orden.puede_facturarse,
                    variant: "primary",
                    onClick: () => onFacturar?.(orden),
                },
            ]}
        />
    );
}


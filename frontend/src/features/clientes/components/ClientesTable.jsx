import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { Badge } from "../../../shared/components/ui/Badge";
import { getClienteDisplayName } from "../utils/clienteForm";

export function ClientesTable({
    clientes = [],
    emptyDescription = "Cuando registres clientes del negocio apareceran en este listado.",
    emptyTitle = "No hay clientes cargados todavia",
    onEdit,
    onView,
}) {
    return (
        <DataTable
            columns={["Nombre", "DNI/CIF", "Tipo", "Email", "Telefono", "Estado", "Acciones"]}
            empty={
                !clientes.length ? (
                    <EmptyState
                        title={emptyTitle}
                        description={emptyDescription}
                    />
                ) : null
            }
        >
            {clientes.map((cliente) => (
                <tr key={cliente.id}>
                    <td>
                        <strong>{getClienteDisplayName(cliente)}</strong>
                        {cliente.persona_contacto ? <small>{cliente.persona_contacto}</small> : null}
                    </td>
                    <td>{cliente.dni_cif}</td>
                    <td>{cliente.tipo_cliente?.nombre || "No indicado"}</td>
                    <td>{cliente.email || "No indicado"}</td>
                    <td>{cliente.telefono || "No indicado"}</td>
                    <td>
                        <Badge tone={cliente.activo ? "success" : "neutral"}>
                            {cliente.activo ? "Activo" : "Inactivo"}
                        </Badge>
                    </td>
                    <td>
                        <RowActionsMenu
                            actions={[
                                {
                                    label: "Ver",
                                    onClick: onView ? () => onView(cliente) : undefined,
                                    to: onView ? undefined : `/app/clientes/${cliente.id}`,
                                    variant: "primary",
                                },
                                {
                                    label: "Editar",
                                    onClick: onEdit ? () => onEdit(cliente) : undefined,
                                    to: onEdit ? undefined : `/app/clientes/${cliente.id}/editar`,
                                },
                            ]}
                        />
                    </td>
                </tr>
            ))}
        </DataTable>
    );
}

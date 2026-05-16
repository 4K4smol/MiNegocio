import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { getClienteDisplayName } from "../utils/clienteForm";

export function ClientesTable({ clientes = [], onEdit }) {
    return (
        <DataTable
            columns={["Nombre", "DNI/CIF", "Tipo", "Email", "Teléfono", "Estado", "Acciones"]}
            empty={
                !clientes.length ? (
                    <EmptyState
                        title="No hay clientes cargados todavía"
                        description="Cuando registres clientes del negocio aparecerán en este listado."
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
                    <td>{cliente.activo ? "Activo" : "Inactivo"}</td>
                    <td>
                        <RowActionsMenu
                            actions={[
                                {
                                    label: "Ver",
                                    to: `/app/clientes/${cliente.id}`,
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

import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";

const getDisplayName = (cliente) =>
    cliente.razon_social || [cliente.nombre, cliente.apellidos].filter(Boolean).join(" ");

export function ClientesTable({ clientes = [] }) {
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
                        <strong>{getDisplayName(cliente)}</strong>
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
                                    to: `/app/clientes/${cliente.id}/editar`,
                                },
                            ]}
                        />
                    </td>
                </tr>
            ))}
        </DataTable>
    );
}

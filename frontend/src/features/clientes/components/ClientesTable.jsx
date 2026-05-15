import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";

export function ClientesTable({ clientes = [] }) {
    return (
        <DataTable
            columns={["Nombre", "Email", "Teléfono", "Acciones"]}
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
                    <td>{cliente.nombre}</td>
                    <td>{cliente.email}</td>
                    <td>{cliente.telefono}</td>
                    <td>
                        <RowActionsMenu
                            actions={[
                                {
                                    label: "Ver",
                                    to: `/app/clientes/${cliente.id}`,
                                    variant: "primary",
                                },
                            ]}
                        />
                    </td>
                </tr>
            ))}
        </DataTable>
    );
}

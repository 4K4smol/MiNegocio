import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";

export function UbicacionesTable({ onEdit, ubicaciones = [] }) {
    return (
        <DataTable
            columns={[
                "Nombre",
                "Descripcion",
                "Observaciones",
                "Acciones",
            ]}
            empty={
                !ubicaciones.length ? (
                    <EmptyState
                        title="No hay ubicaciones registradas"
                        description="Crea ubicaciones fisicas para organizar stock y traslados."
                    />
                ) : null
            }
        >
            {ubicaciones.map((ubicacion) => (
                <tr key={ubicacion.id}>
                    <td>
                        <strong>{ubicacion.nombre}</strong>
                    </td>
                    <td>{ubicacion.descripcion || "Sin descripcion"}</td>
                    <td>{ubicacion.observaciones || "Sin observaciones"}</td>
                    <td>
                        <RowActionsMenu
                            actions={[
                                {
                                    label: "Editar",
                                    onClick: () => onEdit(ubicacion),
                                },
                            ]}
                        />
                    </td>
                </tr>
            ))}
        </DataTable>
    );
}

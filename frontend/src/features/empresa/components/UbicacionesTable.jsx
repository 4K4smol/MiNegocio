import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";

export function UbicacionesTable({ onEdit, onToggleActivo, ubicaciones = [] }) {
    return (
        <DataTable
            columns={[
                "Nombre",
                "Direccion",
                "Descripcion",
                "Estado",
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
                    <td>{ubicacion.direccion || "Sin direccion"}</td>
                    <td>{ubicacion.descripcion || "Sin descripcion"}</td>
                    <td>
                        <StatusBadge
                            status={ubicacion.activo ? "activo" : "inactivo"}
                        />
                    </td>
                    <td>
                        <RowActionsMenu
                            actions={[
                                {
                                    label: "Editar",
                                    onClick: () => onEdit(ubicacion),
                                },
                                {
                                    label: ubicacion.activo
                                        ? "Desactivar"
                                        : "Activar",
                                    variant: ubicacion.activo
                                        ? "danger"
                                        : "primary",
                                    onClick: () => onToggleActivo(ubicacion),
                                },
                            ]}
                        />
                    </td>
                </tr>
            ))}
        </DataTable>
    );
}

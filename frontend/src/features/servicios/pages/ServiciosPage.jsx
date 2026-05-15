import { Link } from "react-router-dom";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { useResourceList } from "../../../shared/hooks/useResourceList";
import { serviciosService } from "../services/serviciosService";

export function ServiciosPage() {
    const { error, items: servicios, loading } = useResourceList(serviciosService.list);

    return (
        <section className="page">
            <PageHeader
                actions={
                    <Link className="button" to="/app/servicios/nuevo">
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nuevo servicio
                    </Link>
                }
                description="Catálogo de servicios, precios y tarifas de la empresa."
                title="Servicios"
            />

            {loading ? <LoadingState>Cargando servicios...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <DataTable
                    columns={["Servicio", "Código", "Unidad", "Duración", "Estado", "Acciones"]}
                    empty={
                        !servicios.length ? (
                            <EmptyState
                                title="No hay servicios configurados"
                                description="Crea los servicios que ofrece tu empresa para usarlos en órdenes y facturas."
                            />
                        ) : null
                    }
                >
                    {servicios.map((servicio) => (
                        <tr key={servicio.id}>
                            <td>
                                <strong>{servicio.nombre}</strong>
                                {servicio.descripcion ? <small>{servicio.descripcion}</small> : null}
                            </td>
                            <td>{servicio.codigo || "Sin código"}</td>
                            <td>{servicio.unidad_servicio || "Servicio"}</td>
                            <td>
                                {servicio.duracion_estimada_min
                                    ? `${servicio.duracion_estimada_min} min`
                                    : "Sin duración"}
                            </td>
                            <td>
                                <StatusBadge status={servicio.activo ? "activo" : "inactivo"} />
                            </td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        {
                                            label: "Editar",
                                            to: `/app/servicios/${servicio.id}/editar`,
                                        },
                                        { label: "Gestionar tarifas", disabled: true },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            ) : null}
        </section>
    );
}

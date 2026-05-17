import { useCallback, useEffect, useState } from "react";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { StatusBadge } from "../../../shared/components/StatusBadge";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { tiposTarifaServicioService } from "../services/serviciosService";

export function TiposTarifaServicioTab() {
    const [tipos, setTipos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    const loadTipos = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setTipos(unwrapApiCollection(await tiposTarifaServicioService.list()));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar los tipos de tarifa.");
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadTipos();
    }, [loadTipos]);

    return (
        <section className="module-section">
            <div className="section-toolbar">
                <div>
                    <h2>Tipos de tarifa disponibles</h2>
                    <p>Los tipos de tarifa son definidos por MiNegocio. Tu empresa solo asigna precios propios a cada servicio.</p>
                </div>
            </div>

            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando tipos de tarifa...</LoadingState>
            ) : (
                <DataTable
                    columns={["Nombre", "Codigo", "Descripcion", "Estado"]}
                    empty={
                        !tipos.length ? (
                            <EmptyState
                                title="No hay tipos de tarifa activos"
                                description="Cuando MiNegocio active tipos de tarifa apareceran aqui."
                            />
                        ) : null
                    }
                >
                    {tipos.map((tipo) => (
                        <tr key={tipo.id}>
                            <td><strong>{tipo.nombre}</strong></td>
                            <td>{tipo.codigo}</td>
                            <td>{tipo.descripcion || "Sin descripcion"}</td>
                            <td><StatusBadge status={tipo.activo ? "activo" : "inactivo"} /></td>
                        </tr>
                    ))}
                </DataTable>
            )}
        </section>
    );
}

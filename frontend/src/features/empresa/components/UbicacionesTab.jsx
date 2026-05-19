import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { UbicacionesTable } from "./UbicacionesTable";

export function UbicacionesTab({
    error,
    loading,
    onCreate,
    onEdit,
    ubicaciones,
}) {
    return (
        <section className="card">
            <div className="page-header-row">
                <div>
                    <h2>Ubicaciones</h2>
                    <p>
                        Gestiona almacenes, talleres, vehiculos o puntos donde
                        se organiza el stock.
                    </p>
                </div>
                <button className="button" type="button" onClick={onCreate}>
                    <AppIcon icon={appIcons.crear} size={18} />
                    Nueva ubicacion
                </button>
            </div>
            {loading ? (
                <LoadingState>Cargando ubicaciones...</LoadingState>
            ) : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {!loading ? (
                <UbicacionesTable
                    ubicaciones={ubicaciones}
                    onEdit={onEdit}
                />
            ) : null}
        </section>
    );
}

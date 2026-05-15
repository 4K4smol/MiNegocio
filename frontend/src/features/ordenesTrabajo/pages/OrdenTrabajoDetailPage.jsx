import { useParams } from "react-router-dom";
import { PlaceholderPage } from "../../../shared/components/PlaceholderPage";

export function OrdenTrabajoDetailPage() {
    const { ordenId } = useParams();

    return (
        <PlaceholderPage
            title="Detalle de orden"
            description={`Detalle de la orden ${ordenId} preparado para ordenesTrabajoService.get.`}
        />
    );
}

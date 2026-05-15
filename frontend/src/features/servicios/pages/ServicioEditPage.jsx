import { useParams } from "react-router-dom";
import { PlaceholderPage } from "../../../shared/components/PlaceholderPage";

export function ServicioEditPage() {
    const { servicioId } = useParams();

    return (
        <PlaceholderPage
            title="Editar servicio"
            description={`Edición del servicio ${servicioId} preparada para serviciosService.update.`}
        />
    );
}

import { useParams } from "react-router-dom";
import { PlaceholderPage } from "../../../shared/components/PlaceholderPage";

export function InventarioEditPage() {
    const { itemId } = useParams();

    return (
        <PlaceholderPage
            title="Editar ítem de inventario"
            description={`Edición del ítem ${itemId} preparada para inventarioService.update.`}
        />
    );
}

import { useParams } from "react-router-dom";
import { ServiciosPage } from "./ServiciosPage";

export function ServicioEditPage() {
    const { servicioId } = useParams();

    return <ServiciosPage initialAction="edit" initialServicioId={servicioId} />;
}

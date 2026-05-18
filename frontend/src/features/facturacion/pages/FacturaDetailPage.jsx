import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { FacturaPreview } from "../../facturas/components/FacturaPreview";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { facturasService } from "../services/facturasService";

export function FacturaDetailPage() {
    const { facturaId } = useParams();
    const [factura, setFactura] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        let mounted = true;
        const load = async () => {
            setLoading(true);
            setError("");
            try {
                const response = await facturasService.get(facturaId);
                if (mounted) setFactura(unwrapApiData(response));
            } catch (currentError) {
                if (mounted) setError(currentError?.message || "No se ha podido cargar la factura.");
            } finally {
                if (mounted) setLoading(false);
            }
        };

        load();

        return () => {
            mounted = false;
        };
    }, [facturaId]);

    return (
        <section className="page">
            <PageHeader
                actions={<Link className="button button-ghost" to="/app/facturas">Volver</Link>}
                description="Factura generada desde orden de trabajo o gestionada por facturacion."
                title="Detalle de factura"
            />
            {loading ? <LoadingState>Cargando factura...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {!loading && factura ? <FacturaPreview factura={factura} /> : null}
        </section>
    );
}

import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { OrdenForm } from "../components/OrdenForm";
import { ordenesApi } from "../services/ordenesApi";

export function OrdenEditPage() {
    const { ordenId } = useParams();
    const navigate = useNavigate();
    const [orden, setOrden] = useState(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [errors, setErrors] = useState({});

    useEffect(() => {
        let mounted = true;
        const load = async () => {
            setLoading(true);
            setError("");
            try {
                const response = await ordenesApi.get(ordenId);
                if (mounted) setOrden(unwrapApiData(response));
            } catch (currentError) {
                if (mounted) setError(currentError?.message || "No se ha podido cargar la orden.");
            } finally {
                if (mounted) setLoading(false);
            }
        };
        load();
        return () => {
            mounted = false;
        };
    }, [ordenId]);

    const handleSubmit = async (payload) => {
        setSaving(true);
        setError("");
        setErrors({});
        try {
            await ordenesApi.update(ordenId, payload);
            navigate(`/app/ordenes-trabajo/${ordenId}`);
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido actualizar la orden.");
            setErrors(currentError?.errors || currentError?.payload?.errors || {});
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                description="Ajusta los datos de programación y las líneas antes de completar la orden."
                title="Editar orden"
            />
            {loading ? <LoadingState>Cargando orden...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}
            {!loading && orden ? (
                <OrdenForm
                    errors={errors}
                    initialOrden={orden}
                    saving={saving}
                    onCancel={() => navigate(`/app/ordenes-trabajo/${ordenId}`)}
                    onSubmit={handleSubmit}
                />
            ) : null}
        </section>
    );
}

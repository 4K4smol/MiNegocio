import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { OrdenForm } from "../components/OrdenForm";
import { ordenesApi } from "../services/ordenesApi";

export function OrdenCreatePage() {
    const navigate = useNavigate();
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [errors, setErrors] = useState({});

    const handleSubmit = async (payload) => {
        setSaving(true);
        setError("");
        setErrors({});
        try {
            const response = await ordenesApi.create(payload);
            const orden = unwrapApiData(response);
            navigate(`/app/ordenes-trabajo/${orden.id || ""}`);
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido crear la orden.");
            setErrors(currentError?.errors || currentError?.payload?.errors || {});
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                description="Selecciona cliente, fecha y servicios para programar el trabajo."
                title="Nueva orden"
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            <OrdenForm
                errors={errors}
                saving={saving}
                onCancel={() => navigate("/app/ordenes-trabajo")}
                onSubmit={handleSubmit}
            />
        </section>
    );
}


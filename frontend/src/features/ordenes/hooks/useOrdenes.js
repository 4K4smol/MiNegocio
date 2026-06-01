import { useCallback, useEffect, useMemo, useState } from "react";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { ordenesApi } from "../services/ordenesApi";

export function useOrdenes() {
    const [ordenes, setOrdenes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [filters, setFilters] = useState({
        cliente_id: "",
        estado: "",
        fecha_desde: "",
        fecha_hasta: "",
    });

    const params = useMemo(() => ({
        per_page: 100,
        cliente_id: filters.cliente_id || undefined,
        estado: filters.estado || undefined,
        desde: filters.fecha_desde || undefined,
        hasta: filters.fecha_hasta || undefined,
    }), [filters]);

    const loadOrdenes = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setOrdenes(unwrapApiCollection(await ordenesApi.list(params)));
        } catch (currentError) {
            setOrdenes([]);
            setError(currentError?.message || "No se han podido cargar las órdenes.");
        } finally {
            setLoading(false);
        }
    }, [params]);

    useEffect(() => {
        loadOrdenes();
    }, [loadOrdenes]);

    return { error, filters, loading, ordenes, reload: loadOrdenes, setFilters };
}

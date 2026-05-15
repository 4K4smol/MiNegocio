import { useCallback, useEffect, useState } from "react";
import { unwrapApiCollection } from "../utils/apiResponse";

export function useResourceList(listFn, params) {
    const [items, setItems] = useState([]);
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        setLoading(true);
        setError("");

        try {
            setItems(unwrapApiCollection(await listFn(params)));
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido cargar la información.");
            setItems([]);
        } finally {
            setLoading(false);
        }
    }, [listFn, params]);

    useEffect(() => {
        load();
    }, [load]);

    return { error, items, loading, reload: load };
}

import { useCallback, useEffect, useMemo, useState } from "react";
import { clientesService } from "../../clientes/services/clientesService";
import { serviciosService } from "../../servicios/services/serviciosService";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";

const today = () => new Date().toISOString().slice(0, 10);

const getOrdenDate = (orden) => {
    const value = orden?.fechas?.programada_inicio || orden?.fecha_programada_inicio;
    if (!value) return today();
    return new Date(value).toISOString().slice(0, 10);
};

const getOrdenTime = (orden) => {
    const value = orden?.fechas?.programada_inicio || orden?.fecha_programada_inicio;
    if (!value) return "09:00";
    return new Date(value).toTimeString().slice(0, 5);
};

const normalizeLine = (linea) => ({
    servicio_id: linea.servicio_id,
    servicio_nombre: linea.servicio_nombre || linea.descripcion || "Servicio",
    descripcion: linea.descripcion || linea.servicio_nombre || "Servicio",
    cantidad: Number(linea.cantidad || 1),
    precio_unitario: Number(linea.precio_unitario || 0),
    descuento_porcentaje: Number(linea.descuento_porcentaje || 0),
    iva_porcentaje: Number(linea.iva_porcentaje ?? 21),
    facturable: linea.facturable !== false,
});

export const calculateLine = (linea) => {
    const cantidad = Number(linea.cantidad || 0);
    const precio = Number(linea.precio_unitario || 0);
    const descuento = Number(linea.descuento_porcentaje || 0);
    const iva = Number(linea.iva_porcentaje || 0);
    const bruto = cantidad * precio;
    const base = Math.max(bruto - (bruto * descuento) / 100, 0);
    const cuotaIva = (base * iva) / 100;

    return {
        base_imponible: Number(base.toFixed(2)),
        cuota_iva: Number(cuotaIva.toFixed(2)),
        total: Number((base + cuotaIva).toFixed(2)),
    };
};

export function useOrdenForm(initialOrden) {
    const [clientes, setClientes] = useState([]);
    const [servicios, setServicios] = useState([]);
    const [catalogLoading, setCatalogLoading] = useState(true);
    const [catalogError, setCatalogError] = useState("");
    const [form, setForm] = useState({
        cliente_id: initialOrden?.cliente?.id || "",
        localizacion_id: "",
        fecha: getOrdenDate(initialOrden),
        hora: getOrdenTime(initialOrden),
        notas_cliente: initialOrden?.notas?.cliente || "",
        notas_internas: initialOrden?.notas?.internas || "",
    });
    const [lineas, setLineas] = useState((initialOrden?.lineas || []).map(normalizeLine));

    useEffect(() => {
        let mounted = true;
        const loadCatalogs = async () => {
            setCatalogLoading(true);
            setCatalogError("");
            try {
                const [clientesResponse, serviciosResponse] = await Promise.all([
                    clientesService.list({ per_page: 100 }),
                    serviciosService.list({ per_page: 100, activo: true, include: "precios" }),
                ]);
                if (!mounted) return;
                setClientes(unwrapApiCollection(clientesResponse));
                setServicios(unwrapApiCollection(serviciosResponse));
            } catch (currentError) {
                if (!mounted) return;
                setCatalogError(currentError?.message || "No se han podido cargar clientes y servicios.");
            } finally {
                if (mounted) setCatalogLoading(false);
            }
        };

        loadCatalogs();

        return () => {
            mounted = false;
        };
    }, []);

    const updateForm = useCallback((field, value) => {
        setForm((current) => ({ ...current, [field]: value }));
    }, []);

    const addServiceLine = useCallback((servicio) => {
        const precios = servicio.precios || [];
        const precioVigente =
            precios.find((precio) => !precio.vigente_hasta) ||
            [...precios].sort((a, b) => String(b.vigente_desde || "").localeCompare(String(a.vigente_desde || "")))[0];

        setLineas((current) => ([
            ...current,
            normalizeLine({
                servicio_id: servicio.id,
                servicio_nombre: servicio.nombre,
                descripcion: servicio.nombre,
                cantidad: 1,
                precio_unitario: precioVigente?.precio_base ?? 0,
                descuento_porcentaje: 0,
                iva_porcentaje: precioVigente?.iva_porcentaje ?? 21,
                facturable: true,
            }),
        ]));
    }, []);

    const updateLine = useCallback((index, field, value) => {
        setLineas((current) => current.map((linea, currentIndex) => (
            currentIndex === index ? { ...linea, [field]: value } : linea
        )));
    }, []);

    const removeLine = useCallback((index) => {
        setLineas((current) => current.filter((_, currentIndex) => currentIndex !== index));
    }, []);

    const totals = useMemo(() => lineas.reduce((acc, linea) => {
        const calculated = calculateLine(linea);
        return {
            base_imponible: acc.base_imponible + calculated.base_imponible,
            cuota_iva: acc.cuota_iva + calculated.cuota_iva,
            total: acc.total + calculated.total,
        };
    }, { base_imponible: 0, cuota_iva: 0, total: 0 }), [lineas]);

    const buildPayload = useCallback(() => {
        const fechaProgramada = form.fecha && form.hora
            ? `${form.fecha}T${form.hora}:00`
            : null;

        return {
            cliente_id: Number(form.cliente_id),
            fecha_programada_inicio: fechaProgramada,
            notas_cliente: form.notas_cliente || null,
            notas_internas: form.notas_internas || null,
            lineas: lineas.map((linea) => ({
                servicio_id: Number(linea.servicio_id),
                descripcion: linea.descripcion || linea.servicio_nombre,
                cantidad: Number(linea.cantidad || 0),
                precio_unitario: Number(linea.precio_unitario || 0),
                descuento_porcentaje: Number(linea.descuento_porcentaje || 0),
                iva_porcentaje: Number(linea.iva_porcentaje || 0),
                facturable: linea.facturable !== false,
            })),
        };
    }, [form, lineas]);

    return {
        addServiceLine,
        buildPayload,
        catalogError,
        catalogLoading,
        clientes,
        form,
        lineas,
        removeLine,
        servicios,
        totals,
        updateForm,
        updateLine,
    };
}


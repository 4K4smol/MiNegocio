import { useState } from "react";
import { formatCurrency } from "../../../shared/utils/formatters";

const precioLabel = (precio) => {
    const tarifa = precio.tipo_tarifa_servicio || precio.tarifa;
    const nombre = tarifa?.nombre || tarifa?.codigo || "Tarifa";
    return `${nombre} - ${formatCurrency(precio.precio_base)}`;
};

export function ServicioSelector({ onSelect, servicio }) {
    const precios = servicio.precios || [];
    const [precioId, setPrecioId] = useState(precios[0]?.id ? String(precios[0].id) : "");
    const selectedPrecio = precios.find((precio) => String(precio.id) === String(precioId));

    return (
        <tr>
            <td>
                <strong>{servicio.nombre}</strong>
                {servicio.codigo ? <small>{servicio.codigo}</small> : null}
            </td>
            <td>{servicio.unidad_servicio || "unidad"}</td>
            <td>
                <select
                    disabled={!precios.length}
                    value={precioId}
                    onChange={(event) => setPrecioId(event.target.value)}
                >
                    {!precios.length ? <option value="">Sin precios configurados</option> : null}
                    {precios.map((precio) => (
                        <option key={precio.id} value={precio.id}>
                            {precioLabel(precio)}
                        </option>
                    ))}
                </select>
            </td>
            <td>
                <button
                    className="button button-ghost"
                    disabled={!selectedPrecio}
                    type="button"
                    onClick={() => onSelect(servicio, selectedPrecio)}
                >
                    Anadir
                </button>
            </td>
        </tr>
    );
}

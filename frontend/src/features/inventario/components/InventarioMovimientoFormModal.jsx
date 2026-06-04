import { useMemo, useState } from "react";
import { FormModal } from "../../../shared/components/FormModal";

const fieldError = (errors, name) => {
    const value = errors?.[name];
    if (!value) return null;
    return Array.isArray(value) ? value[0] : value;
};

const positiveExistencias = (item) =>
    (item?.existencias || []).filter((existencia) => Number(existencia.cantidad || 0) > 0);

const ubicacionLabel = (ubicacion, item) => {
    const existencia = (item?.existencias || []).find((current) => String(current.ubicacion_id) === String(ubicacion.id));
    const cantidad = existencia ? Number(existencia.cantidad || 0) : null;

    return cantidad !== null ? `${ubicacion.nombre} (${cantidad})` : ubicacion.nombre;
};

export function InventarioMovimientoFormModal({
    disabled = false,
    error,
    errors = {},
    item,
    loading = false,
    onClose,
    onSubmit,
    open,
    tiposMovimiento = [],
    ubicaciones = [],
}) {
    const [tipoId, setTipoId] = useState("");
    const tipoSeleccionado = useMemo(
        () => tiposMovimiento.find((tipo) => String(tipo.id) === String(tipoId)),
        [tipoId, tiposMovimiento],
    );
    const codigo = tipoSeleccionado?.codigo;
    const isAjuste = codigo === "ajuste";
    const isTraslado = codigo === "traslado";
    const isEntrada = codigo === "entrada";
    const isSalida = codigo === "salida";
    const defaultUbicacionId = positiveExistencias(item)[0]?.ubicacion_id || item?.ubicacion_id || "";
    const showOrigen = isSalida || isAjuste || isTraslado;
    const showDestino = isEntrada || isTraslado;

    return (
        <FormModal
            error={error}
            loading={loading}
            mode="create"
            open={open}
            submitLabel="Registrar movimiento"
            title="Registrar movimiento"
            subtitle={item?.nombre}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <input name="inventario_item_id" type="hidden" value={item?.id || ""} />
            <div className="form-grid">
                <label>
                    Tipo de movimiento
                    <select disabled={disabled} name="tipo_movimiento_id" required value={tipoId} onChange={(event) => setTipoId(event.target.value)}>
                        <option value="">Selecciona tipo</option>
                        {tiposMovimiento.map((tipo) => (
                            <option key={tipo.id} value={tipo.id}>{tipo.nombre}</option>
                        ))}
                    </select>
                    {fieldError(errors, "tipo_movimiento_id") ? <small className="field-error">{fieldError(errors, "tipo_movimiento_id")}</small> : null}
                </label>

                <label>
                    {isAjuste ? "Nuevo stock en ubicacion" : "Cantidad"}
                    <input disabled={disabled} min={isAjuste ? "0" : "0.01"} name="cantidad" required step="0.01" type="number" />
                    {fieldError(errors, "cantidad") ? <small className="field-error">{fieldError(errors, "cantidad")}</small> : null}
                </label>

                {isAjuste ? (
                    <label>
                        Stock posterior
                        <input disabled={disabled} min="0" name="stock_posterior" step="0.01" type="number" />
                        <small className="field-help">Opcional. Si lo dejas vacio se usara el campo Nuevo stock.</small>
                        {fieldError(errors, "stock_posterior") ? <small className="field-error">{fieldError(errors, "stock_posterior")}</small> : null}
                    </label>
                ) : null}

                {showOrigen ? (
                    <label>
                        {isAjuste ? "Ubicacion" : "Ubicacion origen"}
                        <select disabled={disabled} name="ubicacion_origen_id" required defaultValue={defaultUbicacionId}>
                            <option value="">{isAjuste ? "Selecciona ubicacion" : "Selecciona origen"}</option>
                            {ubicaciones.map((ubicacion) => (
                                <option key={ubicacion.id} value={ubicacion.id}>{ubicacionLabel(ubicacion, item)}</option>
                            ))}
                        </select>
                        {fieldError(errors, "ubicacion_origen_id") ? <small className="field-error">{fieldError(errors, "ubicacion_origen_id")}</small> : null}
                    </label>
                ) : null}

                {showDestino ? (
                    <label>
                        Ubicacion destino
                        <select disabled={disabled} name="ubicacion_destino_id" required defaultValue={isEntrada ? defaultUbicacionId : ""}>
                            <option value="">Selecciona destino</option>
                            {ubicaciones.map((ubicacion) => (
                                <option key={ubicacion.id} value={ubicacion.id}>{ubicacionLabel(ubicacion, item)}</option>
                            ))}
                        </select>
                        {fieldError(errors, "ubicacion_destino_id") ? <small className="field-error">{fieldError(errors, "ubicacion_destino_id")}</small> : null}
                    </label>
                ) : null}

                <label>
                    Fecha
                    <input disabled={disabled} name="fecha_movimiento" type="datetime-local" />
                    {fieldError(errors, "fecha_movimiento") ? <small className="field-error">{fieldError(errors, "fecha_movimiento")}</small> : null}
                </label>

                <label className="is-wide">
                    Motivo
                    <textarea disabled={disabled} name="motivo" rows={3} />
                    {fieldError(errors, "motivo") ? <small className="field-error">{fieldError(errors, "motivo")}</small> : null}
                </label>
            </div>
        </FormModal>
    );
}

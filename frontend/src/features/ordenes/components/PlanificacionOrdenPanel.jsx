const PRIORIDADES = [
    { codigo: "baja", nombre: "Baja" },
    { codigo: "normal", nombre: "Normal" },
    { codigo: "alta", nombre: "Alta" },
    { codigo: "urgente", nombre: "Urgente" },
];

export function PlanificacionOrdenPanel({ disabled = false, errors = {}, form, onChange }) {
    return (
        <div className="form-grid">
            <label>
                Fecha prevista
                <input
                    disabled={disabled}
                    type="date"
                    value={form.fecha}
                    onChange={(event) => onChange("fecha", event.target.value)}
                />
                {errors.fecha_programada_inicio ? <small className="field-error">{errors.fecha_programada_inicio[0]}</small> : null}
            </label>

            <label>
                Hora inicio
                <input
                    disabled={disabled}
                    type="time"
                    value={form.hora_inicio}
                    onChange={(event) => onChange("hora_inicio", event.target.value)}
                />
            </label>

            <label>
                Hora fin
                <input
                    disabled={disabled}
                    type="time"
                    value={form.hora_fin}
                    onChange={(event) => onChange("hora_fin", event.target.value)}
                />
                {errors.fecha_programada_fin ? <small className="field-error">{errors.fecha_programada_fin[0]}</small> : null}
            </label>

            <label>
                Prioridad
                <select
                    disabled={disabled}
                    value={form.prioridad_codigo}
                    onChange={(event) => onChange("prioridad_codigo", event.target.value)}
                >
                    {PRIORIDADES.map((prioridad) => (
                        <option key={prioridad.codigo} value={prioridad.codigo}>
                            {prioridad.nombre}
                        </option>
                    ))}
                </select>
            </label>

            <label className="is-wide">
                Notas para el cliente
                <textarea
                    disabled={disabled}
                    placeholder="Instrucciones o informacion visible para el cliente..."
                    rows={3}
                    value={form.notas_cliente}
                    onChange={(event) => onChange("notas_cliente", event.target.value)}
                />
            </label>

            <label className="is-wide">
                Notas internas
                <textarea
                    disabled={disabled}
                    placeholder="Notas solo visibles para el equipo..."
                    rows={3}
                    value={form.notas_internas}
                    onChange={(event) => onChange("notas_internas", event.target.value)}
                />
            </label>
        </div>
    );
}

export { PRIORIDADES };

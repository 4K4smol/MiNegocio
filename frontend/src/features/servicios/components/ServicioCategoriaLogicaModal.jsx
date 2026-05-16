import { FormModal } from "../../../shared/components/FormModal";

export function ServicioCategoriaLogicaModal({
    categorias = [],
    categoria,
    error,
    loading = false,
    mode,
    onClose,
    onSubmit,
    open,
}) {
    const isFusion = mode === "fusionar";
    const title = isFusion ? "Fusionar categoria" : "Renombrar categoria";

    return (
        <FormModal
            error={error}
            loading={loading}
            mode="edit"
            open={open}
            submitLabel={isFusion ? "Fusionar" : "Renombrar"}
            title={title}
            onClose={onClose}
            onSubmit={onSubmit}
        >
            <input name="actual" readOnly type="hidden" value={categoria?.nombre || ""} />
            {isFusion ? (
                <label>
                    Categoria destino
                    <select defaultValue="" disabled={loading} name="destino" required>
                        <option value="">Selecciona una categoria</option>
                        {categorias
                            .filter((item) => item.nombre !== categoria?.nombre)
                            .map((item) => (
                                <option key={item.nombre} value={item.nombre}>
                                    {item.nombre}
                                </option>
                            ))}
                    </select>
                </label>
            ) : (
                <label>
                    Nuevo nombre
                    <input defaultValue={categoria?.nombre || ""} disabled={loading} name="nuevo" required />
                </label>
            )}
        </FormModal>
    );
}

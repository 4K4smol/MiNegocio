export function ClienteForm({
    initialValues = {},
    onSubmit,
    isSubmitting = false,
}) {
    const handleSubmit = (event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        onSubmit?.(Object.fromEntries(formData.entries()));
    };

    return (
        <form className="form" onSubmit={handleSubmit}>
            <label>
                Nombre
                <input
                    name="nombre"
                    defaultValue={initialValues.nombre || ""}
                />
            </label>
            <label>
                Email
                <input
                    name="email"
                    type="email"
                    defaultValue={initialValues.email || ""}
                />
            </label>
            <label>
                Teléfono
                <input
                    name="telefono"
                    defaultValue={initialValues.telefono || ""}
                />
            </label>
            <button className="button" type="submit" disabled={isSubmitting}>
                Guardar cliente
            </button>
        </form>
    );
}

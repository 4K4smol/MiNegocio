import { useCallback, useEffect, useMemo, useState } from "react";
import { AdminActionsMenu } from "./AdminActionsMenu";
import { AdminConfirmModal } from "./AdminConfirmModal";
import { AdminDataTable } from "./AdminDataTable";
import { AdminFiltersBar } from "./AdminFiltersBar";
import { EmptyState } from "./EmptyState";
import { ErrorState } from "./ErrorState";
import { LoadingState } from "./LoadingState";

const initialValue = (fields) =>
    Object.fromEntries(fields.map((field) => [field.name, field.defaultValue ?? (field.type === "checkbox" ? false : "")]));

const normalizeResponse = (response) => {
    if (Array.isArray(response)) return response;
    if (Array.isArray(response?.items)) return response.items;
    if (Array.isArray(response?.data)) return response.data;
    return [];
};

const valueForField = (value, field) => {
    if (field.type === "number") return value === "" || value === null ? null : Number(value);
    if (field.type === "checkbox") return Boolean(value);
    return String(value ?? "").trim() || null;
};

function CatalogFormModal({ config, mode, value, error, loading, onChange, onClose, onSubmit }) {
    if (!mode) return null;

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <form className="admin-modal config-modal" onSubmit={onSubmit}>
                <header>
                    <div>
                        <span className="admin-kicker">{config.title}</span>
                        <h3>{mode === "create" ? `Nuevo ${config.singular}` : `Editar ${config.singular}`}</h3>
                    </div>
                    <button type="button" className="admin-icon-button admin-button-ghost" onClick={onClose} aria-label="Cerrar" disabled={loading}>
                        X
                    </button>
                </header>
                <div className="config-form-grid">
                    {config.fields.map((field) => (
                        <label key={field.name} className={field.wide ? "is-wide" : field.type === "checkbox" ? "config-checkbox" : undefined}>
                            {field.type === "checkbox" ? (
                                <>
                                    <input
                                        type="checkbox"
                                        checked={Boolean(value[field.name])}
                                        onChange={(event) => onChange({ ...value, [field.name]: event.target.checked })}
                                    />
                                    <span>{field.label}</span>
                                </>
                            ) : (
                                <>
                                    <span>{field.label}</span>
                                    {field.type === "textarea" ? (
                                        <textarea
                                            value={value[field.name] ?? ""}
                                            onChange={(event) => onChange({ ...value, [field.name]: event.target.value })}
                                            required={field.required}
                                        />
                                    ) : (
                                        <input
                                            type={field.type || "text"}
                                            min={field.min}
                                            value={value[field.name] ?? ""}
                                            onChange={(event) => onChange({ ...value, [field.name]: event.target.value })}
                                            required={field.required}
                                        />
                                    )}
                                </>
                            )}
                        </label>
                    ))}
                </div>
                {error ? <ErrorState>{error}</ErrorState> : null}
                <footer>
                    <button type="button" className="admin-button admin-button-ghost" onClick={onClose} disabled={loading}>
                        Cancelar
                    </button>
                    <button type="submit" className="admin-button" disabled={loading}>
                        {loading ? "Guardando..." : "Guardar"}
                    </button>
                </footer>
            </form>
        </div>
    );
}

export function AdminCatalogCrud({ config, onSuccess }) {
    const [items, setItems] = useState([]);
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [formMode, setFormMode] = useState(null);
    const [formValue, setFormValue] = useState(initialValue(config.fields));
    const [formError, setFormError] = useState("");
    const [confirm, setConfirm] = useState(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setItems(normalizeResponse(await config.listFunction({ per_page: 100 })));
        } catch (apiError) {
            setError(apiError.message || `No se ha podido cargar ${config.title}.`);
        } finally {
            setLoading(false);
        }
    }, [config]);

    useEffect(() => {
        load();
    }, [load]);

    const filteredItems = useMemo(() => {
        const query = search.trim().toLowerCase();
        if (!query) return items;

        return items.filter((item) =>
            config.searchKeys.some((key) => String(item[key] || "").toLowerCase().includes(query)),
        );
    }, [config.searchKeys, items, search]);

    const openCreate = () => {
        setFormMode("create");
        setFormValue(initialValue(config.fields));
        setFormError("");
    };

    const openEdit = (item) => {
        setFormMode("edit");
        setFormValue({
            id: item.id,
            ...Object.fromEntries(config.fields.map((field) => [field.name, item[field.name] ?? field.defaultValue ?? ""])),
        });
        setFormError("");
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setFormError("");
    };

    const payloadFromForm = () =>
        Object.fromEntries(config.fields.map((field) => [field.name, valueForField(formValue[field.name], field)]));

    const submitForm = async (event) => {
        event.preventDefault();
        setSaving(true);
        setFormError("");
        try {
            const payload = payloadFromForm();
            if (formMode === "create") {
                await config.createFunction(payload);
                onSuccess?.(`${config.singular} creado correctamente.`);
            } else {
                await config.updateFunction(formValue.id, payload);
                onSuccess?.(`${config.singular} actualizado correctamente.`);
            }
            setFormMode(null);
            await load();
        } catch (apiError) {
            setFormError(apiError.message || `No se ha podido guardar ${config.singular}.`);
        } finally {
            setSaving(false);
        }
    };

    const executeConfirm = async () => {
        setSaving(true);
        setError("");
        try {
            await confirm.action();
            onSuccess?.(confirm.successMessage);
            setConfirm(null);
            await load();
        } catch (apiError) {
            setError(apiError.message || `No se ha podido actualizar ${config.singular}.`);
        } finally {
            setSaving(false);
        }
    };

    if (loading) return <LoadingState>Cargando {config.title.toLowerCase()}...</LoadingState>;

    return (
        <section className="admin-page">
            {error ? <ErrorState>{error}</ErrorState> : null}
            <AdminFiltersBar>
                <input placeholder={`Buscar en ${config.title.toLowerCase()}`} value={search} onChange={(event) => setSearch(event.target.value)} />
                <div className="config-toolbar-spacer" />
                <button type="button" className="admin-button" onClick={openCreate}>
                    Nuevo
                </button>
            </AdminFiltersBar>
            <AdminDataTable
                columns={[...config.columns.map((column) => column.label), "Acciones"]}
                empty={!filteredItems.length ? <EmptyState title={`Sin ${config.title.toLowerCase()}`} /> : null}
            >
                {filteredItems.map((item) => (
                    <tr key={item.id}>
                        {config.columns.map((column) => (
                            <td key={column.key}>{column.render ? column.render(item) : item[column.key] || "No indicado"}</td>
                        ))}
                        <td>
                            <AdminActionsMenu
                                actions={[
                                    { label: "Editar", onClick: () => openEdit(item) },
                                    {
                                        label: item.activo ? "Desactivar" : "Activar",
                                        variant: item.activo ? "danger" : "success",
                                        hidden: !config.activateFunction || !config.deactivateFunction,
                                        onClick: () => setConfirm({
                                            title: `${item.activo ? "Desactivar" : "Activar"} ${config.singular}`,
                                            description: `Confirma la accion sobre ${item.nombre || item.codigo}.`,
                                            action: () => item.activo ? config.deactivateFunction(item.id) : config.activateFunction(item.id),
                                            successMessage: `${config.singular} actualizado correctamente.`,
                                        }),
                                    },
                                ]}
                            />
                        </td>
                    </tr>
                ))}
            </AdminDataTable>

            <CatalogFormModal
                config={config}
                mode={formMode}
                value={formValue}
                error={formError}
                loading={saving}
                onChange={setFormValue}
                onClose={closeForm}
                onSubmit={submitForm}
            />
            <AdminConfirmModal
                open={Boolean(confirm)}
                title={confirm?.title}
                description={confirm?.description}
                loading={saving}
                onCancel={() => setConfirm(null)}
                onConfirm={executeConfirm}
            />
        </section>
    );
}

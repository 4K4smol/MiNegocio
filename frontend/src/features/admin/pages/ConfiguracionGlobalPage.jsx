import { useEffect, useMemo, useState } from "react";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminConfirmModal } from "../components/AdminConfirmModal";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminFiltersBar } from "../components/AdminFiltersBar";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

const TABS = [
    ["modulos", "Modulos"],
    ["roles", "Roles"],
    ["estados_verificacion", "Estados de verificacion"],
    ["tipos_empresa", "Tipos de empresa"],
    ["tipos_documento_identidad", "Tipos de documento"],
    ["otros", "Otros catalogos"],
];

const EMPTY_FORM = {
    codigo: "",
    nombre: "",
    descripcion: "",
    orden_visual: "",
    icono: "",
    activo: true,
};

const formatDate = (value) =>
    value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium" }).format(new Date(value)) : "Sin fecha";

function ModuloFormModal({ mode, value, error, loading, onChange, onClose, onSubmit }) {
    if (!mode) return null;

    return (
        <div className="admin-modal-backdrop" role="presentation">
            <form className="admin-modal config-modal" onSubmit={onSubmit}>
                <header>
                    <div>
                        <span className="admin-kicker">Modulo</span>
                        <h3>{mode === "create" ? "Nuevo modulo" : "Editar modulo"}</h3>
                    </div>
                    <button type="button" className="admin-icon-button admin-button-ghost" onClick={onClose} aria-label="Cerrar" disabled={loading}>
                        X
                    </button>
                </header>
                <div className="config-form-grid">
                    <label>
                        <span>Codigo</span>
                        <input value={value.codigo} onChange={(event) => onChange({ ...value, codigo: event.target.value })} required />
                    </label>
                    <label>
                        <span>Nombre</span>
                        <input value={value.nombre} onChange={(event) => onChange({ ...value, nombre: event.target.value })} required />
                    </label>
                    <label className="is-wide">
                        <span>Descripcion</span>
                        <textarea value={value.descripcion} onChange={(event) => onChange({ ...value, descripcion: event.target.value })} />
                    </label>
                    <label>
                        <span>Orden visual</span>
                        <input
                            type="number"
                            min="0"
                            value={value.orden_visual}
                            onChange={(event) => onChange({ ...value, orden_visual: event.target.value })}
                        />
                    </label>
                    <label>
                        <span>Icono</span>
                        <input value={value.icono} onChange={(event) => onChange({ ...value, icono: event.target.value })} />
                    </label>
                    <label className="config-checkbox">
                        <input
                            type="checkbox"
                            checked={value.activo}
                            onChange={(event) => onChange({ ...value, activo: event.target.checked })}
                        />
                        <span>Modulo activo</span>
                    </label>
                </div>
                {error ? <ErrorState>{error}</ErrorState> : null}
                <footer>
                    <button type="button" className="admin-button admin-button-ghost" onClick={onClose} disabled={loading}>
                        Cancelar
                    </button>
                    <button type="submit" className="admin-button" disabled={loading}>
                        {loading ? "Guardando..." : "Guardar modulo"}
                    </button>
                </footer>
            </form>
        </div>
    );
}

export function ConfiguracionGlobalPage() {
    const [activeTab, setActiveTab] = useState("modulos");
    const [catalogos, setCatalogos] = useState({});
    const [modulos, setModulos] = useState([]);
    const [roles, setRoles] = useState([]);
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");
    const [confirm, setConfirm] = useState(null);
    const [formMode, setFormMode] = useState(null);
    const [formValue, setFormValue] = useState(EMPTY_FORM);
    const [formError, setFormError] = useState("");

    const load = async () => {
        setLoading(true);
        setError("");
        try {
            const [catalogosResponse, modulosResponse, rolesResponse] = await Promise.all([
                adminApi.getAdminCatalogos(),
                adminApi.getAdminModulos(),
                adminApi.getAdminRoles?.({ per_page: 100 }),
            ]);
            setCatalogos(catalogosResponse || {});
            setModulos(Array.isArray(modulosResponse) ? modulosResponse : modulosResponse?.data || []);
            setRoles(rolesResponse?.items || catalogosResponse?.roles || []);
        } catch (apiError) {
            setError(apiError.message || "No se ha podido cargar la configuracion.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const filteredModulos = useMemo(() => {
        const query = search.trim().toLowerCase();
        if (!query) return modulos;

        return modulos.filter((modulo) =>
            [modulo.codigo, modulo.nombre, modulo.descripcion, modulo.icono]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(query)),
        );
    }, [modulos, search]);

    const catalogoActual = useMemo(() => {
        if (activeTab === "roles") return roles;
        if (activeTab === "otros") {
            return Object.entries(catalogos)
                .filter(([key, value]) => Array.isArray(value) && !["roles", "estados_verificacion", "tipos_empresa", "tipos_documento_identidad", "modulos"].includes(key))
                .flatMap(([key, value]) => value.map((item) => ({ ...item, catalogo: key })));
        }
        return catalogos[activeTab] || [];
    }, [activeTab, catalogos, roles]);

    const openCreate = () => {
        setFormMode("create");
        setFormValue(EMPTY_FORM);
        setFormError("");
    };

    const openEdit = (modulo) => {
        setFormMode("edit");
        setFormValue({
            id: modulo.id,
            codigo: modulo.codigo || "",
            nombre: modulo.nombre || "",
            descripcion: modulo.descripcion || "",
            orden_visual: modulo.orden_visual ?? "",
            icono: modulo.icono || "",
            activo: Boolean(modulo.activo),
        });
        setFormError("");
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setFormError("");
    };

    const payloadFromForm = () => ({
        codigo: formValue.codigo.trim(),
        nombre: formValue.nombre.trim(),
        descripcion: formValue.descripcion.trim() || null,
        orden_visual: formValue.orden_visual === "" ? null : Number(formValue.orden_visual),
        icono: formValue.icono.trim() || null,
        activo: Boolean(formValue.activo),
    });

    const submitForm = async (event) => {
        event.preventDefault();
        setFormError("");
        setSuccess("");

        const payload = payloadFromForm();
        if (!payload.codigo || !payload.nombre) {
            setFormError("Codigo y nombre son obligatorios.");
            return;
        }

        if (payload.orden_visual !== null && Number.isNaN(payload.orden_visual)) {
            setFormError("El orden visual debe ser numerico.");
            return;
        }

        setSaving(true);
        try {
            if (formMode === "create") {
                await adminApi.crearModulo(payload);
                setSuccess("Modulo creado correctamente.");
            } else {
                await adminApi.actualizarModulo(formValue.id, payload);
                setSuccess("Modulo actualizado correctamente.");
            }
            setFormMode(null);
            await load();
        } catch (apiError) {
            setFormError(apiError.message || "No se ha podido guardar el modulo.");
        } finally {
            setSaving(false);
        }
    };

    const executeConfirm = async () => {
        setSaving(true);
        setError("");
        setSuccess("");
        try {
            await confirm.action();
            setSuccess(confirm.successMessage || "Operacion completada correctamente.");
            setConfirm(null);
            await load();
        } catch (apiError) {
            setError(apiError.message || "No se ha podido actualizar el modulo.");
        } finally {
            setSaving(false);
        }
    };

    if (loading) return <LoadingState>Cargando configuracion...</LoadingState>;

    return (
        <section className="admin-page admin-configuracion-page">
            <AdminPageHeader
                title="Configuracion global"
                description="Gestiona los catalogos y parametros base de MiNegocio."
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            {success ? <div className="admin-success-alert">{success}</div> : null}

            <nav className="admin-tabs" aria-label="Secciones de configuracion">
                {TABS.map(([id, label]) => (
                    <button key={id} type="button" className={activeTab === id ? "is-active" : ""} onClick={() => setActiveTab(id)}>
                        {label}
                    </button>
                ))}
            </nav>

            {activeTab === "modulos" ? (
                <>
                    <AdminFiltersBar>
                        <input placeholder="Buscar por codigo, nombre o descripcion" value={search} onChange={(event) => setSearch(event.target.value)} />
                        <div className="config-toolbar-spacer" />
                        <button type="button" className="admin-button" onClick={openCreate}>
                            Nuevo modulo
                        </button>
                    </AdminFiltersBar>

                    <AdminDataTable
                        columns={["Codigo", "Nombre", "Descripcion", "Orden visual", "Icono", "Estado", "Acciones"]}
                        empty={!filteredModulos.length ? <EmptyState title="Sin modulos" description="No hay modulos que coincidan con la busqueda." /> : null}
                    >
                        {filteredModulos.map((modulo) => (
                            <tr key={modulo.id}>
                                <td><strong>{modulo.codigo}</strong></td>
                                <td>{modulo.nombre}</td>
                                <td>{modulo.descripcion || "Sin descripcion"}</td>
                                <td>{modulo.orden_visual ?? "No indicado"}</td>
                                <td>{modulo.icono || "Sin icono"}</td>
                                <td><AdminStatusBadge estado={modulo.activo ? "activa" : "inactiva"} /></td>
                                <td>
                                    <AdminActionsMenu actions={[
                                        { label: "Editar", onClick: () => openEdit(modulo), variant: "default" },
                                        {
                                            label: modulo.activo ? "Desactivar" : "Activar",
                                            variant: modulo.activo ? "danger" : "success",
                                            onClick: () => setConfirm({
                                                title: `${modulo.activo ? "Desactivar" : "Activar"} modulo`,
                                                description: modulo.activo
                                                    ? `Confirma que quieres desactivar ${modulo.nombre}.`
                                                    : `Confirma que quieres activar ${modulo.nombre}.`,
                                                action: () => modulo.activo ? adminApi.desactivarModulo(modulo.id) : adminApi.activarModulo(modulo.id),
                                                successMessage: modulo.activo ? "Modulo desactivado correctamente." : "Modulo activado correctamente.",
                                            }),
                                        },
                                    ]} />
                                </td>
                            </tr>
                        ))}
                    </AdminDataTable>
                </>
            ) : null}

            {activeTab === "roles" ? (
                <section className="admin-card catalog-readonly">
                    <div className="admin-card-header">
                        <div>
                            <h3>Roles</h3>
                            <p>Los roles son un catalogo sensible. Modificarlos puede afectar al acceso de usuarios.</p>
                        </div>
                        <span className="readonly-pill">Solo lectura</span>
                    </div>
                    <AdminDataTable
                        columns={["Nombre", "Descripcion", "Fecha creacion"]}
                        empty={!roles.length ? <EmptyState title="Sin roles" /> : null}
                    >
                        {roles.map((role) => (
                            <tr key={role.id}>
                                <td><strong>{role.nombre}</strong></td>
                                <td>{role.descripcion || "Sin descripcion"}</td>
                                <td>{formatDate(role.created_at)}</td>
                            </tr>
                        ))}
                    </AdminDataTable>
                </section>
            ) : null}

            {!["modulos", "roles"].includes(activeTab) ? (
                <section className="admin-card catalog-readonly">
                    <div className="admin-card-header">
                        <div>
                            <h3>{TABS.find(([id]) => id === activeTab)?.[1]}</h3>
                            <p>Catalogo de solo lectura usado por los flujos principales de MiNegocio.</p>
                        </div>
                        <span className="readonly-pill">Solo lectura</span>
                    </div>
                    {catalogoActual.length ? (
                        <div className="catalog-grid">
                            {catalogoActual.map((item) => (
                                <article key={`${item.catalogo || activeTab}-${item.id || item.nombre}`}>
                                    <strong>{item.nombre || item.descripcion}</strong>
                                    {item.descripcion ? <p>{item.descripcion}</p> : null}
                                    {item.catalogo ? <small>{item.catalogo}</small> : null}
                                </article>
                            ))}
                        </div>
                    ) : (
                        <EmptyState title="Sin elementos" description="Este catalogo no contiene datos disponibles." />
                    )}
                </section>
            ) : null}

            <ModuloFormModal
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

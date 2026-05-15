import { useEffect, useMemo, useState } from "react";
import { adminApi } from "../api/adminApi";
import { AdminCatalogCrud } from "../components/AdminCatalogCrud";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

const TABS = [
    ["modulos", "Modulos"],
    ["tipos_cliente", "Tipos de cliente"],
    ["tipos_localizacion", "Tipos de localizacion"],
    ["tipos_empresa", "Tipos de empresa"],
    ["tipos_documento_identidad", "Tipos de documento"],
    ["roles", "Roles"],
    ["estados_verificacion", "Estados de verificacion"],
];

const textColumn = (key, label) => ({
    key,
    label,
    render: (item) => item[key] || "No indicado",
});

const catalogStatusColumn = {
    key: "activo",
    label: "Estado",
    render: (item) => <AdminStatusBadge estado={item.activo ? "activa" : "inactiva"} />,
};

const commonCatalogFields = [
    { name: "codigo", label: "Codigo", required: true },
    { name: "nombre", label: "Nombre", required: true },
    { name: "descripcion", label: "Descripcion", type: "textarea", wide: true },
    { name: "orden", label: "Orden", type: "number", min: 1, defaultValue: 1 },
    { name: "activo", label: "Activo", type: "checkbox", defaultValue: true },
];

const formatDate = (value) =>
    value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium" }).format(new Date(value)) : "Sin fecha";

function ReadonlyCatalog({ title, description, items, columns }) {
    return (
        <section className="admin-card catalog-readonly">
            <div className="admin-card-header">
                <div>
                    <h3>{title}</h3>
                    <p>{description}</p>
                </div>
                <span className="readonly-pill">Solo lectura</span>
            </div>
            <AdminDataTable
                columns={columns.map((column) => column.label)}
                empty={!items.length ? <EmptyState title="Sin elementos" /> : null}
            >
                {items.map((item) => (
                    <tr key={item.id || item.nombre}>
                        {columns.map((column) => (
                            <td key={column.key}>{column.render ? column.render(item) : item[column.key] || "No indicado"}</td>
                        ))}
                    </tr>
                ))}
            </AdminDataTable>
        </section>
    );
}

export function ConfiguracionGlobalPage() {
    const [activeTab, setActiveTab] = useState("modulos");
    const [readonlyCatalogs, setReadonlyCatalogs] = useState({
        roles: [],
        estados_verificacion: [],
        tipos_empresa: [],
        tipos_documento_identidad: [],
    });
    const [loadingReadonly, setLoadingReadonly] = useState(true);
    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");

    const catalogConfigs = useMemo(() => ({
        modulos: {
            title: "Modulos",
            singular: "modulo",
            listFunction: adminApi.getAdminModulos,
            createFunction: adminApi.crearModulo,
            updateFunction: adminApi.actualizarModulo,
            activateFunction: adminApi.activarModulo,
            deactivateFunction: adminApi.desactivarModulo,
            searchKeys: ["codigo", "nombre", "descripcion", "icono"],
            columns: [
                textColumn("codigo", "Codigo"),
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
                textColumn("orden_visual", "Orden visual"),
                textColumn("icono", "Icono"),
                catalogStatusColumn,
            ],
            fields: [
                { name: "codigo", label: "Codigo", required: true },
                { name: "nombre", label: "Nombre", required: true },
                { name: "descripcion", label: "Descripcion", type: "textarea", wide: true },
                { name: "orden_visual", label: "Orden visual", type: "number", min: 0, defaultValue: 1 },
                { name: "icono", label: "Icono" },
                { name: "activo", label: "Modulo activo", type: "checkbox", defaultValue: true },
            ],
        },
        tipos_cliente: {
            title: "Tipos de cliente",
            singular: "tipo de cliente",
            listFunction: adminApi.getTiposCliente,
            createFunction: adminApi.crearTipoCliente,
            updateFunction: adminApi.actualizarTipoCliente,
            activateFunction: adminApi.activarTipoCliente,
            deactivateFunction: adminApi.desactivarTipoCliente,
            searchKeys: ["codigo", "nombre", "descripcion"],
            columns: [
                textColumn("codigo", "Codigo"),
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
                textColumn("orden", "Orden"),
                catalogStatusColumn,
            ],
            fields: commonCatalogFields,
        },
        tipos_localizacion: {
            title: "Tipos de localizacion",
            singular: "tipo de localizacion",
            listFunction: adminApi.getTiposLocalizacionCliente,
            createFunction: adminApi.crearTipoLocalizacionCliente,
            updateFunction: adminApi.actualizarTipoLocalizacionCliente,
            activateFunction: adminApi.activarTipoLocalizacionCliente,
            deactivateFunction: adminApi.desactivarTipoLocalizacionCliente,
            searchKeys: ["codigo", "nombre", "descripcion"],
            columns: [
                textColumn("codigo", "Codigo"),
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
                textColumn("orden", "Orden"),
                catalogStatusColumn,
            ],
            fields: commonCatalogFields,
        },
    }), []);

    useEffect(() => {
        const loadReadonly = async () => {
            setLoadingReadonly(true);
            setError("");
            try {
                const [catalogos, tiposEmpresa, tiposDocumento] = await Promise.all([
                    adminApi.getAdminCatalogos(),
                    adminApi.getTiposEmpresa({ per_page: 100 }),
                    adminApi.getTiposDocumentoIdentidad({ per_page: 100 }),
                ]);

                setReadonlyCatalogs({
                    roles: catalogos?.roles || [],
                    estados_verificacion: catalogos?.estados_verificacion || [],
                    tipos_empresa: tiposEmpresa?.items || catalogos?.tipos_empresa || [],
                    tipos_documento_identidad: tiposDocumento?.items || catalogos?.tipos_documento_identidad || [],
                });
            } catch (apiError) {
                setError(apiError.message || "No se ha podido cargar la configuracion.");
            } finally {
                setLoadingReadonly(false);
            }
        };

        loadReadonly();
    }, []);

    const readonlyColumns = [
        textColumn("nombre", "Nombre"),
        textColumn("descripcion", "Descripcion"),
        { key: "created_at", label: "Fecha creacion", render: (item) => formatDate(item.created_at) },
    ];

    const renderTab = () => {
        if (catalogConfigs[activeTab]) {
            return <AdminCatalogCrud config={catalogConfigs[activeTab]} onSuccess={setSuccess} />;
        }

        if (loadingReadonly) return <LoadingState>Cargando catalogos...</LoadingState>;

        const labels = Object.fromEntries(TABS);
        const descriptions = {
            roles: "Los roles son sensibles porque gobiernan el acceso de usuarios.",
            estados_verificacion: "Estados usados por los flujos de alta, revision y subsanacion.",
            tipos_empresa: "Catalogo usado por el registro y por las reglas de representacion.",
            tipos_documento_identidad: "Catalogo usado por la verificacion de identidad.",
        };

        return (
            <ReadonlyCatalog
                title={labels[activeTab]}
                description={descriptions[activeTab]}
                items={readonlyCatalogs[activeTab] || []}
                columns={readonlyColumns}
            />
        );
    };

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

            {renderTab()}
        </section>
    );
}

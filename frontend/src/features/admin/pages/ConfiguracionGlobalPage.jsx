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
    ["tipos_empresa", "Tipos de empresa"],
    ["tipos_documento_identidad", "Tipos de documento"],
    ["tipos_evento_facturacion", "Tipos de evento"],
    ["tipos_factura", "Tipos de factura"],
    ["tipos_rectificacion", "Tipos de rectificacion"],
    ["tipos_registro_facturacion", "Tipos de registro"],
    ["roles", "Roles"],
    ["estados_verificacion", "Estados de verificacion"],
    ["tipos_tarifa_servicio", "Tipos de tarifa de servicio"],
    ["inventario_unidades_medida", "Unidades de inventario"],
    ["tipos_inventario_movimiento", "Tipos de movimiento"],
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

const nameCatalogFields = [
    { name: "nombre", label: "Nombre", required: true },
    { name: "descripcion", label: "Descripcion", type: "textarea", wide: true },
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
        tipos_tarifa_servicio: {
            title: "Tipos de tarifa de servicio",
            singular: "tipo de tarifa",
            description: "Gestiona los tipos de tarifa globales que podran usar las empresas al definir precios de servicios.",
            listFunction: adminApi.getAdminTiposTarifaServicio,
            createFunction: adminApi.crearAdminTipoTarifaServicio,
            updateFunction: adminApi.actualizarAdminTipoTarifaServicio,
            activateFunction: adminApi.activarAdminTipoTarifaServicio,
            deactivateFunction: adminApi.desactivarAdminTipoTarifaServicio,
            searchKeys: ["codigo", "nombre", "descripcion"],
            columns: [
                textColumn("codigo", "Codigo"),
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
                textColumn("orden", "Orden"),
                catalogStatusColumn,
            ],
            fields: [
                { name: "codigo", label: "Codigo", required: true },
                { name: "nombre", label: "Nombre", required: true },
                { name: "descripcion", label: "Descripcion", type: "textarea", wide: true },
                { name: "orden", label: "Orden", type: "number", min: 0, defaultValue: 0 },
                { name: "activo", label: "Activo", type: "checkbox", defaultValue: true },
            ],
        },
        tipos_empresa: {
            title: "Tipos de empresa",
            singular: "tipo de empresa",
            listFunction: adminApi.getTiposEmpresa,
            createFunction: adminApi.crearTipoEmpresa,
            updateFunction: adminApi.actualizarTipoEmpresa,
            searchKeys: ["nombre", "descripcion"],
            columns: [
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
            ],
            fields: nameCatalogFields,
        },
        tipos_documento_identidad: {
            title: "Tipos de documento",
            singular: "tipo de documento",
            listFunction: adminApi.getTiposDocumentoIdentidad,
            createFunction: adminApi.crearTipoDocumentoIdentidad,
            updateFunction: adminApi.actualizarTipoDocumentoIdentidad,
            searchKeys: ["nombre", "descripcion"],
            columns: [
                textColumn("nombre", "Nombre"),
                textColumn("descripcion", "Descripcion"),
            ],
            fields: nameCatalogFields,
        },
        tipos_evento_facturacion: {
            title: "Tipos de evento",
            singular: "tipo de evento",
            listFunction: adminApi.getTiposEventoFacturacion,
            createFunction: adminApi.crearTipoEventoFacturacion,
            updateFunction: adminApi.actualizarTipoEventoFacturacion,
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
        tipos_factura: {
            title: "Tipos de factura",
            singular: "tipo de factura",
            listFunction: adminApi.getTiposFactura,
            createFunction: adminApi.crearTipoFactura,
            updateFunction: adminApi.actualizarTipoFactura,
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
        tipos_rectificacion: {
            title: "Tipos de rectificacion",
            singular: "tipo de rectificacion",
            listFunction: adminApi.getTiposRectificacion,
            createFunction: adminApi.crearTipoRectificacion,
            updateFunction: adminApi.actualizarTipoRectificacion,
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
        tipos_registro_facturacion: {
            title: "Tipos de registro",
            singular: "tipo de registro",
            listFunction: adminApi.getTiposRegistroFacturacion,
            createFunction: adminApi.crearTipoRegistroFacturacion,
            updateFunction: adminApi.actualizarTipoRegistroFacturacion,
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
        inventario_unidades_medida: {
            title: "Unidades de inventario",
            singular: "unidad de medida",
            description: "Gestiona las unidades globales disponibles para los items de inventario.",
            listFunction: adminApi.getInventarioUnidadesMedida,
            createFunction: adminApi.crearInventarioUnidadMedida,
            updateFunction: adminApi.actualizarInventarioUnidadMedida,
            searchKeys: ["codigo", "nombre"],
            columns: [
                textColumn("codigo", "Codigo"),
                textColumn("nombre", "Nombre"),
            ],
            fields: [
                { name: "codigo", label: "Codigo", required: true },
                { name: "nombre", label: "Nombre", required: true },
            ],
        },
        tipos_inventario_movimiento: {
            title: "Tipos de movimiento",
            singular: "tipo de movimiento",
            description: "Gestiona los tipos globales usados al registrar entradas, salidas, ajustes y traslados.",
            listFunction: adminApi.getTiposInventarioMovimiento,
            createFunction: adminApi.crearTipoInventarioMovimiento,
            updateFunction: adminApi.actualizarTipoInventarioMovimiento,
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
                const catalogos = await adminApi.getAdminCatalogos();

                setReadonlyCatalogs({
                    roles: catalogos?.roles || [],
                    estados_verificacion: catalogos?.estados_verificacion || [],
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

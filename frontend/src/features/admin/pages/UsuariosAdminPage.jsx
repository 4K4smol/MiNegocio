import { useEffect, useMemo, useState } from "react";
import { Modal } from "../../../shared/components/ui/Modal";
import { useAuth } from "../../../shared/hooks/useAuth";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminConfirmModal } from "../components/AdminConfirmModal";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminFiltersBar } from "../components/AdminFiltersBar";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminPagination } from "../components/AdminPagination";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { EmptyState } from "../components/EmptyState";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

const formatDate = (value) => value ? new Intl.DateTimeFormat("es-ES", { dateStyle: "medium" }).format(new Date(value)) : "Sin fecha";
const fullName = (user) => [user.nombre || user.name, user.apellido1, user.apellido2].filter(Boolean).join(" ") || "Sin nombre";

export function UsuariosAdminPage() {
    const { usuario: currentUser } = useAuth();
    const [filters, setFilters] = useState({ texto: "", rol: "", activo: "", empresa: "" });
    const [page, setPage] = useState(1);
    const [usuarios, setUsuarios] = useState([]);
    const [meta, setMeta] = useState(null);
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [confirm, setConfirm] = useState(null);
    const [selected, setSelected] = useState(null);

    const load = async () => {
        setLoading(true);
        setError("");
        try {
            const [usersResponse, catalogos] = await Promise.all([
                adminApi.getAdminUsuarios({ ...filters, page, per_page: 15 }),
                adminApi.getAdminCatalogos(),
            ]);
            setUsuarios(usersResponse.items);
            setMeta(usersResponse.meta);
            setRoles(catalogos.roles || []);
        } catch (apiError) {
            setError(apiError.message || "No se han podido cargar los usuarios.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters, page]);

    const execute = async (label, action) => {
        setSaving(true);
        setError("");
        try {
            await action();
            setConfirm(null);
            await load();
        } catch (apiError) {
            setError(apiError.message || `No se ha podido ${label}.`);
        } finally {
            setSaving(false);
        }
    };

    const roleOptions = useMemo(() => roles.map((role) => ({ value: role.id, label: role.nombre })), [roles]);

    if (loading && !usuarios.length) return <LoadingState>Cargando usuarios...</LoadingState>;

    return (
        <section className="admin-page">
            <AdminPageHeader title="Usuarios" description="Consulta usuarios registrados, estado de acceso y roles." />
            <AdminFiltersBar>
                <input placeholder="Buscar nombre, email o empresa" value={filters.texto} onChange={(event) => { setFilters({ ...filters, texto: event.target.value }); setPage(1); }} />
                <select value={filters.rol} onChange={(event) => { setFilters({ ...filters, rol: event.target.value }); setPage(1); }}>
                    <option value="">Todos los roles</option>
                    {roles.map((role) => <option key={role.id} value={role.nombre}>{role.nombre}</option>)}
                </select>
                <select value={filters.activo} onChange={(event) => { setFilters({ ...filters, activo: event.target.value }); setPage(1); }}>
                    <option value="">Todos</option>
                    <option value="true">Activos</option>
                    <option value="false">Inactivos</option>
                </select>
                <input placeholder="Empresa" value={filters.empresa} onChange={(event) => { setFilters({ ...filters, empresa: event.target.value }); setPage(1); }} />
            </AdminFiltersBar>
            {error ? <ErrorState>{error}</ErrorState> : null}
            <AdminDataTable
                columns={["Nombre", "Email", "Rol", "Empresa", "Estado", "Alta", "Acciones"]}
                empty={!usuarios.length ? <EmptyState title="No hay usuarios" description="Ajusta los filtros para ampliar la búsqueda." /> : null}
            >
                {usuarios.map((user) => (
                    <tr key={user.id}>
                        <td><strong>{fullName(user)}</strong></td>
                        <td>{user.email}</td>
                        <td>{user.role?.nombre || "Sin rol"}</td>
                        <td>{user.empresa?.nombre_fiscal || "Sin empresa"}</td>
                        <td><AdminStatusBadge estado={user.activo ? "activa" : "inactiva"} /></td>
                        <td>{formatDate(user.created_at)}</td>
                        <td>
                            <AdminActionsMenu actions={[
                                {
                                    label: "Ver detalle",
                                    variant: "primary",
                                    onClick: () => setSelected(user),
                                },
                                {
                                    label: user.activo ? "Desactivar" : "Activar",
                                    variant: user.activo ? "warning" : "success",
                                    hidden: currentUser?.id === user.id && user.activo,
                                    onClick: () => setConfirm({
                                        title: `${user.activo ? "Desactivar" : "Activar"} usuario`,
                                        description: `${fullName(user)} (${user.email})`,
                                        action: () => user.activo ? adminApi.desactivarUsuario(user.id) : adminApi.activarUsuario(user.id),
                                    }),
                                },
                                ...roleOptions
                                    .filter((role) => role.value !== user.role?.id)
                                    .slice(0, 4)
                                    .map((role) => ({
                                        label: `Cambiar rol a ${role.label}`,
                                        onClick: () => setConfirm({
                                            title: "Cambiar rol",
                                            description: `${fullName(user)} pasará a ${role.label}.`,
                                            action: () => adminApi.cambiarRolUsuario(user.id, role.value),
                                        }),
                                    })),
                            ]} />
                        </td>
                    </tr>
                ))}
            </AdminDataTable>
            <AdminPagination meta={meta} disabled={loading} onPageChange={setPage} />
            <AdminConfirmModal
                open={Boolean(confirm)}
                title={confirm?.title}
                description={confirm?.description}
                loading={saving}
                onCancel={() => setConfirm(null)}
                onConfirm={() => execute("actualizar el usuario", confirm.action)}
            />
            {selected ? (
                <Modal open size="lg" subtitle="Usuario" title={fullName(selected)} onClose={() => setSelected(null)}>
                    <dl className="admin-data-list">
                        <div><dt>Email</dt><dd>{selected.email}</dd></div>
                        <div><dt>Teléfono</dt><dd>{selected.telefono || "No indicado"}</dd></div>
                        <div><dt>Rol</dt><dd>{selected.role?.nombre || "Sin rol"}</dd></div>
                        <div><dt>Empresa</dt><dd>{selected.empresa?.nombre_fiscal || "Sin empresa"}</dd></div>
                        <div><dt>Estado</dt><dd>{selected.activo ? "Activo" : "No activo"}</dd></div>
                        <div><dt>Alta</dt><dd>{formatDate(selected.created_at)}</dd></div>
                    </dl>
                </Modal>
            ) : null}
        </section>
    );
}

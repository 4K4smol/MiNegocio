import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { adminApi } from "../api/adminApi";
import { AdminActionsMenu } from "../components/AdminActionsMenu";
import { AdminConfirmModal } from "../components/AdminConfirmModal";
import { AdminDataTable } from "../components/AdminDataTable";
import { AdminPageHeader } from "../components/AdminPageHeader";
import { AdminStatusBadge } from "../components/AdminStatusBadge";
import { ErrorState } from "../components/ErrorState";
import { LoadingState } from "../components/LoadingState";

export function EmpresaDetalleAdminPage() {
    const { id } = useParams();
    const [empresa, setEmpresa] = useState(null);
    const [modulos, setModulos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [confirm, setConfirm] = useState(null);

    const load = async () => {
        setLoading(true);
        setError("");
        try {
            const [detalle, modulosResponse] = await Promise.all([
                adminApi.getAdminEmpresaDetalle(id),
                adminApi.getEmpresaModulos(id),
            ]);
            setEmpresa(detalle);
            setModulos(modulosResponse.modulos || []);
        } catch (apiError) {
            setError(apiError.message || "No se ha podido cargar la empresa.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [id]);

    const execute = async () => {
        setSaving(true);
        try {
            await confirm.action();
            setConfirm(null);
            await load();
        } catch (apiError) {
            setError(apiError.message || "No se ha podido actualizar.");
        } finally {
            setSaving(false);
        }
    };

    if (loading) return <LoadingState>Cargando empresa...</LoadingState>;
    if (error && !empresa) return <ErrorState>{error}</ErrorState>;

    return (
        <section className="admin-page">
            <AdminPageHeader title={empresa.nombre_fiscal} description="Detalle administrativo de empresa, usuarios, módulos y solicitudes vinculadas." />
            {error ? <ErrorState>{error}</ErrorState> : null}
            <div className="admin-detail-grid">
                <section className="admin-card admin-data-card">
                    <h3>Datos fiscales</h3>
                    <dl>
                        <dt>NIF</dt><dd>{empresa.nif}</dd>
                        <dt>Nombre comercial</dt><dd>{empresa.nombre_comercial || "No indicado"}</dd>
                        <dt>Tipo</dt><dd>{empresa.tipo_empresa?.nombre || "No indicado"}</dd>
                        <dt>Correo</dt><dd>{empresa.correo || "No indicado"}</dd>
                        <dt>Teléfono</dt><dd>{empresa.telefono || "No indicado"}</dd>
                        <dt>Municipio</dt><dd>{empresa.municipio || "No indicado"}</dd>
                        <dt>Estado</dt><dd><AdminStatusBadge estado={empresa.activa ? "activa" : "inactiva"} /></dd>
                    </dl>
                </section>
                <section className="admin-card admin-data-card">
                    <h3>Usuarios asociados</h3>
                    <ul className="admin-list admin-user-list">
                        {(empresa.usuarios || []).map((usuario) => (
                            <li key={usuario.id}>
                                <div>
                                    <strong>{usuario.nombre || "Sin nombre"}</strong>
                                    <small>{usuario.email}</small>
                                </div>
                                <div>
                                    <span>{usuario.role || "Sin rol"}</span>
                                    <AdminStatusBadge estado={usuario.activo ? "activa" : "inactiva"} />
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>

            <AdminDataTable columns={["Módulo", "Código", "Global", "Empresa", "Acciones"]}>
                {modulos.map((modulo) => (
                    <tr key={modulo.id}>
                        <td><strong>{modulo.nombre}</strong><small>{modulo.descripcion}</small></td>
                        <td>{modulo.codigo}</td>
                        <td><AdminStatusBadge estado={modulo.activo ? "activa" : "inactiva"} /></td>
                        <td><AdminStatusBadge estado={modulo.activo_empresa ? "activa" : "inactiva"} /></td>
                        <td>
                            <AdminActionsMenu actions={[
                                {
                                    label: modulo.activo_empresa ? "Desactivar en empresa" : "Activar en empresa",
                                    variant: modulo.activo_empresa ? "warning" : "success",
                                    onClick: () => setConfirm({
                                        title: `${modulo.activo_empresa ? "Desactivar" : "Activar"} módulo`,
                                        description: `${modulo.nombre} en ${empresa.nombre_fiscal}`,
                                        action: () => modulo.activo_empresa
                                            ? adminApi.desactivarModuloEmpresa(empresa.id, modulo.codigo)
                                            : adminApi.activarModuloEmpresa(empresa.id, modulo.codigo),
                                    }),
                                },
                            ]} />
                        </td>
                    </tr>
                ))}
            </AdminDataTable>

            <section className="admin-card historial-section">
                <h3>Solicitudes vinculadas</h3>
                {(empresa.solicitudes_verificacion || []).length ? (
                    <div className="history-list">
                        {empresa.solicitudes_verificacion.map((solicitud) => (
                            <article key={solicitud.id}>
                                <strong>{solicitud.estado_verificacion}</strong>
                                <small>{solicitud.created_at}</small>
                            </article>
                        ))}
                    </div>
                ) : <p className="admin-empty-inline">Sin solicitudes vinculadas.</p>}
            </section>

            <AdminConfirmModal
                open={Boolean(confirm)}
                title={confirm?.title}
                description={confirm?.description}
                loading={saving}
                onCancel={() => setConfirm(null)}
                onConfirm={execute}
            />
        </section>
    );
}

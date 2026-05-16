import { useCallback, useEffect, useState } from "react";
import { ConfirmModal } from "../../../shared/components/ConfirmModal";
import { DataTable } from "../../../shared/components/DataTable";
import { EmptyState } from "../../../shared/components/EmptyState";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { RowActionsMenu } from "../../../shared/components/RowActionsMenu";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { servicioCategoriasLogicasService } from "../services/serviciosService";
import { ServicioCategoriaLogicaModal } from "./ServicioCategoriaLogicaModal";

export function CategoriasServiciosTab() {
    const [categorias, setCategorias] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [formError, setFormError] = useState("");
    const [modalMode, setModalMode] = useState(null);
    const [selectedCategoria, setSelectedCategoria] = useState(null);
    const [categoriaToEmpty, setCategoriaToEmpty] = useState(null);

    const loadCategorias = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setCategorias(unwrapApiCollection(await servicioCategoriasLogicasService.list()));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar las categorias.");
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadCategorias();
    }, [loadCategorias]);

    const openModal = (mode, categoria) => {
        setSelectedCategoria(categoria);
        setModalMode(mode);
        setFormError("");
    };

    const closeModal = () => {
        if (saving) return;
        setModalMode(null);
        setSelectedCategoria(null);
        setFormError("");
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        setFormError("");
        const formData = new FormData(event.currentTarget);

        try {
            if (modalMode === "fusionar") {
                await servicioCategoriasLogicasService.fusionar({
                    origen: formData.get("actual"),
                    destino: formData.get("destino"),
                });
            } else {
                await servicioCategoriasLogicasService.renombrar({
                    actual: formData.get("actual"),
                    nuevo: formData.get("nuevo"),
                });
            }
            setModalMode(null);
            setSelectedCategoria(null);
            await loadCategorias();
        } catch (currentError) {
            setFormError(currentError?.message || "No se ha podido actualizar la categoria.");
        } finally {
            setSaving(false);
        }
    };

    const handleVaciar = async () => {
        if (!categoriaToEmpty) return;
        setSaving(true);
        try {
            await servicioCategoriasLogicasService.vaciar({ categoria: categoriaToEmpty.nombre });
            setCategoriaToEmpty(null);
            await loadCategorias();
        } catch (currentError) {
            setError(currentError?.message || "No se ha podido vaciar la categoria.");
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="module-section">
            <div className="section-toolbar">
                <div>
                    <h2>Categorias</h2>
                    <p>Organiza los servicios usando el campo categoria del propio servicio.</p>
                </div>
            </div>
            <div className="notice-card">
                Las categorias se generan a partir del campo Categoria de los servicios. Una categoria existe cuando al menos un servicio la utiliza.
            </div>

            {error ? <ErrorState>{error}</ErrorState> : null}
            {loading ? (
                <LoadingState>Cargando categorias...</LoadingState>
            ) : (
                <DataTable
                    columns={["Categoria", "Servicios asociados", "Servicios activos", "Acciones"]}
                    empty={
                        !categorias.length ? (
                            <EmptyState
                                title="No hay categorias"
                                description="Asigna una categoria al crear o editar un servicio para verla aqui."
                            />
                        ) : null
                    }
                >
                    {categorias.map((categoria) => (
                        <tr key={categoria.nombre}>
                            <td><strong>{categoria.nombre}</strong></td>
                            <td>{categoria.total_servicios}</td>
                            <td>{categoria.servicios_activos}</td>
                            <td>
                                <RowActionsMenu
                                    actions={[
                                        { label: "Renombrar", onClick: () => openModal("renombrar", categoria) },
                                        {
                                            label: "Fusionar",
                                            hidden: categorias.length < 2,
                                            onClick: () => openModal("fusionar", categoria),
                                        },
                                        {
                                            label: "Vaciar categoria",
                                            variant: "danger",
                                            onClick: () => setCategoriaToEmpty(categoria),
                                        },
                                    ]}
                                />
                            </td>
                        </tr>
                    ))}
                </DataTable>
            )}

            <ServicioCategoriaLogicaModal
                categorias={categorias}
                categoria={selectedCategoria}
                error={formError}
                loading={saving}
                mode={modalMode}
                open={Boolean(modalMode)}
                onClose={closeModal}
                onSubmit={handleSubmit}
            />

            <ConfirmModal
                confirmLabel="Vaciar categoria"
                description="Los servicios conservaran sus datos, pero quedaran sin categoria."
                loading={saving}
                open={Boolean(categoriaToEmpty)}
                title="Vaciar categoria"
                onCancel={() => setCategoriaToEmpty(null)}
                onConfirm={handleVaciar}
            />
        </section>
    );
}

import { useCallback, useEffect, useMemo, useState } from "react";
import { AppIcon } from "../../../components/ui/AppIcon";
import { appIcons } from "../../../config/appIcons";
import { ErrorState } from "../../../shared/components/ErrorState";
import { FormModal } from "../../../shared/components/FormModal";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiCollection } from "../../../shared/utils/apiResponse";
import { ClienteDetailModal } from "../components/ClienteDetailModal";
import { ClienteForm } from "../components/ClienteForm";
import { ClientesTable } from "../components/ClientesTable";
import { clientesService } from "../services/clientesService";
import { clientePayloadFromForm, validationErrors } from "../utils/clienteForm";

export function ClientesPage() {
    const [clientes, setClientes] = useState([]);
    const [tiposCliente, setTiposCliente] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingTipos, setLoadingTipos] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState("");
    const [tiposError, setTiposError] = useState("");
    const [formError, setFormError] = useState("");
    const [formErrors, setFormErrors] = useState({});
    const [formMode, setFormMode] = useState(null);
    const [selectedCliente, setSelectedCliente] = useState(null);
    const [selectedClienteForView, setSelectedClienteForView] = useState(null);
    const [searchTerm, setSearchTerm] = useState("");
    const [statusFilter, setStatusFilter] = useState("todos");

    const loadClientes = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            setClientes(unwrapApiCollection(await clientesService.list({ per_page: 100 })));
        } catch (currentError) {
            setError(currentError?.message || "No se han podido cargar los clientes.");
        } finally {
            setLoading(false);
        }
    }, []);

    const loadTiposCliente = useCallback(async () => {
        setLoadingTipos(true);
        setTiposError("");
        try {
            setTiposCliente(unwrapApiCollection(await clientesService.getTiposCliente({ per_page: 100 })));
        } catch (currentError) {
            setTiposError(currentError?.message || "No se han podido cargar los tipos de cliente.");
        } finally {
            setLoadingTipos(false);
        }
    }, []);

    useEffect(() => {
        loadClientes();
        loadTiposCliente();
    }, [loadClientes, loadTiposCliente]);

    const filteredClientes = useMemo(() => {
        const query = searchTerm.trim().toLowerCase();

        return clientes.filter((cliente) => {
            const matchesSearch = !query || [
                cliente.nombre,
                cliente.apellidos,
                cliente.razon_social,
                cliente.dni_cif,
                cliente.email,
                cliente.telefono,
            ].some((value) => String(value || "").toLowerCase().includes(query));

            const matchesStatus =
                statusFilter === "todos"
                || (statusFilter === "activos" && cliente.activo)
                || (statusFilter === "inactivos" && !cliente.activo);

            return matchesSearch && matchesStatus;
        });
    }, [clientes, searchTerm, statusFilter]);

    const hasActiveFilters = Boolean(searchTerm.trim()) || statusFilter !== "todos";

    const resetFormState = () => {
        setFormError("");
        setFormErrors({});
    };

    const openCreate = () => {
        setSelectedCliente(null);
        setSelectedClienteForView(null);
        setFormMode("create");
        resetFormState();
    };

    const openEdit = (cliente) => {
        setSelectedCliente(cliente);
        setSelectedClienteForView(null);
        setFormMode("edit");
        resetFormState();
    };

    const openView = (cliente) => {
        setSelectedClienteForView(cliente);
    };

    const closeView = () => {
        setSelectedClienteForView(null);
    };

    const closeForm = () => {
        if (saving) return;
        setFormMode(null);
        setSelectedCliente(null);
        resetFormState();
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSaving(true);
        resetFormState();

        try {
            const payload = clientePayloadFromForm(event.currentTarget);
            if (formMode === "edit" && selectedCliente?.id) {
                await clientesService.update(selectedCliente.id, payload);
            } else {
                await clientesService.create(payload);
            }
            setFormMode(null);
            setSelectedCliente(null);
            resetFormState();
            await loadClientes();
        } catch (currentError) {
            setFormErrors(validationErrors(currentError));
            setFormError(currentError?.message || "No se ha podido guardar el cliente.");
        } finally {
            setSaving(false);
        }
    };

    return (
        <section className="page">
            <PageHeader
                actions={(
                    <button className="button" type="button" onClick={openCreate}>
                        <AppIcon icon={appIcons.crear} size={18} />
                        Nuevo cliente
                    </button>
                )}
                description="Consulta y gestiona los clientes de tu empresa."
                title="Clientes"
            />
            {error ? <ErrorState>{error}</ErrorState> : null}
            <section className="filters-bar clientes-filters" aria-label="Filtros de clientes">
                <input
                    placeholder="Buscar por nombre, razon social o DNI/CIF"
                    value={searchTerm}
                    onChange={(event) => setSearchTerm(event.target.value)}
                />
                <select value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)}>
                    <option value="todos">Todos</option>
                    <option value="activos">Activos</option>
                    <option value="inactivos">Inactivos</option>
                </select>
            </section>
            {loading ? (
                <LoadingState>Cargando clientes...</LoadingState>
            ) : (
                <ClientesTable
                    clientes={filteredClientes}
                    emptyDescription={hasActiveFilters ? "Ajusta los filtros o modifica la busqueda para ver mas resultados." : undefined}
                    emptyTitle={hasActiveFilters ? "No hay clientes que coincidan con la busqueda." : undefined}
                    onEdit={openEdit}
                    onView={openView}
                />
            )}

            <FormModal
                error={formError || tiposError}
                loading={saving}
                mode={formMode || "create"}
                open={Boolean(formMode)}
                submitDisabled={loadingTipos || Boolean(tiposError)}
                submitLabel={formMode === "edit" ? "Guardar cambios" : "Crear cliente"}
                title={formMode === "edit" ? "Editar cliente" : "Nuevo cliente"}
                onClose={closeForm}
                onSubmit={handleSubmit}
            >
                {loadingTipos ? (
                    <LoadingState>Cargando tipos de cliente...</LoadingState>
                ) : (
                    <ClienteForm
                        disabled={saving || Boolean(tiposError)}
                        errors={formErrors}
                        initialValues={selectedCliente || {}}
                        key={`${formMode || "create"}-${selectedCliente?.id || "new"}`}
                        tiposCliente={tiposCliente}
                    />
                )}
            </FormModal>
            <ClienteDetailModal
                cliente={selectedClienteForView}
                onClose={closeView}
                onEdit={openEdit}
            />
        </section>
    );
}

import { useCallback, useEffect, useMemo, useState } from "react";
import { useParams } from "react-router-dom";
import { adminSolicitudesApi, previewDocumentoVerificacion } from "../api/adminSolicitudesApi";
import { AdminDecisionModal } from "../components/AdminDecisionModal";
import { AdminPagination } from "../components/AdminPagination";
import { DocumentoPreviewModal } from "../components/DocumentoPreviewModal";
import { SolicitudDetallePanel } from "../components/SolicitudDetallePanel";
import { SolicitudesFilters } from "../components/SolicitudesFilters";
import { SolicitudesTable } from "../components/SolicitudesTable";

const INITIAL_FILTERS = {
    texto: "",
    estado: "",
    tipo_empresa: "",
    orden: "desc",
};

const SUMMARY_STATES = [
    ["pendiente", "Pendientes"],
    ["en_revision", "En revisión"],
    ["subsanacion", "Subsanación"],
    ["aprobada", "Aprobadas"],
    ["rechazada", "Rechazadas"],
];

const getErrorMessage = (error, fallback) => error?.message || fallback;

export function AdminSolicitudesPage() {
    const { id: routeEmpresaId } = useParams();
    const [filters, setFilters] = useState(INITIAL_FILTERS);
    const [page, setPage] = useState(1);
    const [solicitudes, setSolicitudes] = useState([]);
    const [meta, setMeta] = useState(null);
    const [selectedId, setSelectedId] = useState(null);
    const [detalle, setDetalle] = useState(null);
    const [loadingList, setLoadingList] = useState(true);
    const [loadingDetail, setLoadingDetail] = useState(false);
    const [savingDecision, setSavingDecision] = useState(false);
    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");
    const [decision, setDecision] = useState(null);
    const [decisionText, setDecisionText] = useState("");
    const [decisionDocuments, setDecisionDocuments] = useState([]);
    const [decisionError, setDecisionError] = useState("");
    const [preview, setPreview] = useState(null);
    const [previewLoading, setPreviewLoading] = useState(false);
    const [previewError, setPreviewError] = useState("");

    const loadSolicitudes = useCallback(async () => {
        setLoadingList(true);
        setError("");
        try {
            const response = await adminSolicitudesApi.listar({ ...filters, page, per_page: 15 });
            setSolicitudes(response.items);
            setMeta(response.meta);
            setSelectedId((current) => current ?? (routeEmpresaId ? Number(routeEmpresaId) : null) ?? response.items[0]?.id ?? null);
        } catch (apiError) {
            setError(getErrorMessage(apiError, "No se han podido cargar las solicitudes."));
        } finally {
            setLoadingList(false);
        }
    }, [filters, page, routeEmpresaId]);

    const loadDetalle = useCallback(async (empresaId) => {
        if (!empresaId) {
            setDetalle(null);
            return;
        }

        setLoadingDetail(true);
        setError("");
        try {
            setDetalle(await adminSolicitudesApi.detalle(empresaId));
        } catch (apiError) {
            setError(getErrorMessage(apiError, "No se ha podido cargar el expediente."));
        } finally {
            setLoadingDetail(false);
        }
    }, []);

    useEffect(() => {
        loadSolicitudes();
    }, [loadSolicitudes]);

    useEffect(() => {
        loadDetalle(selectedId);
    }, [loadDetalle, selectedId]);

    useEffect(() => () => {
        if (preview?.url) URL.revokeObjectURL(preview.url);
    }, [preview]);

    const summary = useMemo(() => {
        const counts = Object.fromEntries(SUMMARY_STATES.map(([state]) => [state, 0]));
        solicitudes.forEach((solicitud) => {
            const estado = solicitud.estado_verificacion || "pendiente";
            if (counts[estado] !== undefined) counts[estado] += 1;
        });
        return counts;
    }, [solicitudes]);

    const openDecision = (type, empresaId = selectedId) => {
        setDecision({ type, empresaId });
        setDecisionText("");
        setDecisionDocuments(type === "subsanacion" ? ["representacion"] : []);
        setDecisionError("");
    };

    const closeDecision = () => {
        if (savingDecision) return;
        setDecision(null);
        setDecisionError("");
    };

    const submitDecision = async (event) => {
        event.preventDefault();
        if (!decision) return;

        const motivo = decisionText.trim();
        if (decision.type !== "aprobar" && motivo.length < 5) {
            setDecisionError("El motivo debe tener al menos 5 caracteres.");
            return;
        }

        if (decision.type === "aprobar" && !window.confirm("¿Confirmas la aprobación de esta solicitud?")) {
            return;
        }

        setSavingDecision(true);
        setDecisionError("");
        setSuccess("");
        try {
            if (decision.type === "aprobar") {
                await adminSolicitudesApi.aprobar(decision.empresaId, { observaciones: motivo || null });
                setSuccess("Solicitud aprobada correctamente.");
            } else if (decision.type === "rechazar") {
                await adminSolicitudesApi.rechazar(decision.empresaId, { motivo });
                setSuccess("Solicitud rechazada correctamente.");
            } else {
                await adminSolicitudesApi.solicitarSubsanacion(decision.empresaId, {
                    motivo,
                    documentos_requeridos: decisionDocuments,
                });
                    setSuccess("Subsanación solicitada correctamente.");
            }

            setDecision(null);
            await loadSolicitudes();
            await loadDetalle(decision.empresaId);
        } catch (apiError) {
            setDecisionError(getErrorMessage(apiError, "No se ha podido completar la decisión."));
        } finally {
            setSavingDecision(false);
        }
    };

    const toggleDecisionDocument = (documentType) => {
        setDecisionDocuments((current) =>
            current.includes(documentType)
                ? current.filter((item) => item !== documentType)
                : [...current, documentType],
        );
    };

    const openPreview = async (documento) => {
        if (preview?.url) URL.revokeObjectURL(preview.url);

        setPreview(null);
        setPreviewError("");
        setPreviewLoading(true);
        try {
            setPreview(await previewDocumentoVerificacion(documento.id));
        } catch (apiError) {
            setPreviewError(getErrorMessage(apiError, "No se ha podido previsualizar el documento."));
        } finally {
            setPreviewLoading(false);
        }
    };

    const closePreview = () => {
        if (preview?.url) URL.revokeObjectURL(preview.url);
        setPreview(null);
        setPreviewError("");
        setPreviewLoading(false);
    };

    return (
        <section className="admin-page solicitudes-page">
            <header className="admin-section-header solicitudes-header">
                <div>
                    <span className="admin-kicker">Administración</span>
                    <h2>Solicitudes de verificación</h2>
                    <p>Revisión de altas de usuarios, empresas y documentación.</p>
                </div>
            </header>

            <section className="admin-metrics-grid solicitudes-summary" aria-label="Resumen de solicitudes">
                {SUMMARY_STATES.map(([state, label]) => (
                    <article className="admin-card admin-metric-card" key={state}>
                        <div>
                            <p>{label}</p>
                            <strong>{summary[state] || 0}</strong>
                        </div>
                    </article>
                ))}
            </section>

            <SolicitudesFilters
                filters={filters}
                onChange={(nextFilters) => {
                    setFilters(nextFilters);
                    setPage(1);
                }}
                loading={loadingList}
            />

            {error ? <div className="form-alert">{error}</div> : null}
            {success ? <div className="admin-success-alert">{success}</div> : null}

            {loadingList ? <p className="admin-loading">Cargando solicitudes...</p> : null}
            {!loadingList && solicitudes.length === 0 ? (
                <section className="admin-card admin-empty-inline">
                    <strong>No hay solicitudes con estos filtros.</strong>
                    <p>Ajusta la búsqueda o cambia el estado seleccionado.</p>
                </section>
            ) : null}

            {solicitudes.length ? (
                <div className="solicitudes-workspace">
                    <SolicitudesTable
                        solicitudes={solicitudes}
                        selectedId={selectedId}
                        onSelect={setSelectedId}
                        onDecision={openDecision}
                    />
                    <AdminPagination meta={meta} disabled={loadingList} onPageChange={setPage} />
                    <SolicitudDetallePanel
                        solicitud={detalle}
                        loading={loadingDetail}
                        onPreviewDocument={openPreview}
                        onDecision={openDecision}
                    />
                </div>
            ) : null}

            <AdminDecisionModal
                type={decision?.type}
                value={decisionText}
                selectedDocuments={decisionDocuments}
                loading={savingDecision}
                error={decisionError}
                onChange={setDecisionText}
                onToggleDocument={toggleDecisionDocument}
                onClose={closeDecision}
                onSubmit={submitDecision}
            />

            <DocumentoPreviewModal
                preview={preview}
                loading={previewLoading}
                error={previewError}
                onClose={closePreview}
            />
        </section>
    );
}

import { useEffect, useMemo, useState } from "react";
import { CalendarMonth } from "../../calendario/components/CalendarMonth";
import { getMonthRange } from "../../calendario/components/calendarUtils";
import { calendarioService } from "../../calendario/services/calendarioService";
import { ordenesTrabajoService } from "../../ordenesTrabajo/services/ordenesTrabajoService";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { unwrapApiData } from "../../../shared/utils/apiResponse";
import { useResourceList } from "../../../shared/hooks/useResourceList";
import { dashboardService } from "../services/dashboardService";
import { BusinessStatusCards } from "../components/BusinessStatusCards";
import { DashboardQuickActions } from "../components/DashboardQuickActions";
import { RecentOrdersCard } from "../components/RecentOrdersCard";
import { UpcomingOrdersCard } from "../components/UpcomingOrdersCard";
import { getRecentCompletedOrders } from "../utils/orderFilters";
import "../styles/dashboard.css";

export function DashboardPage() {
    const [monthDate, setMonthDate] = useState(() => new Date());
    const [businessSummary, setBusinessSummary] = useState({});
    const [loadingSummary, setLoadingSummary] = useState(true);
    const [summaryError, setSummaryError] = useState("");
    const calendarParams = useMemo(() => getMonthRange(monthDate), [monthDate]);
    const upcomingParams = useMemo(() => ({ limite: 5 }), []);
    const ordersParams = useMemo(() => ({ per_page: 50 }), []);

    const {
        error: calendarError,
        items: calendarEvents,
        loading: loadingCalendar,
    } = useResourceList(calendarioService.dashboardCalendario, calendarParams);

    const {
        error: upcomingError,
        items: upcomingOrders,
        loading: loadingUpcoming,
    } = useResourceList(dashboardService.proximasOrdenes, upcomingParams);

    const {
        error: recentError,
        items: allOrders,
        loading: loadingRecent,
    } = useResourceList(ordenesTrabajoService.list, ordersParams);

    const recentOrders = useMemo(() => getRecentCompletedOrders(allOrders), [allOrders]);
    const isDashboardLoading = (loadingCalendar || loadingUpcoming || loadingRecent || loadingSummary) && !calendarError;

    useEffect(() => {
        let mounted = true;

        dashboardService.resumen()
            .then((response) => {
                if (mounted) setBusinessSummary(unwrapApiData(response) || {});
            })
            .catch((currentError) => {
                if (mounted) {
                    setSummaryError(currentError?.message || "No se ha podido cargar el resumen del negocio.");
                    setBusinessSummary({});
                }
            })
            .finally(() => {
                if (mounted) setLoadingSummary(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    const changeMonth = (amount) => {
        setMonthDate((current) => new Date(current.getFullYear(), current.getMonth() + amount, 1));
    };

    if (isDashboardLoading) {
        return (
            <section className="page dashboard-page">
                <PageHeader
                    description="Planifica y gestiona la actividad diaria de tu empresa."
                    title="Dashboard"
                />
                <LoadingState>Cargando panel de control...</LoadingState>
            </section>
        );
    }

    return (
        <section className="page dashboard-page">
            <PageHeader
                description="Planifica y gestiona la actividad diaria de tu empresa."
                title="Dashboard"
            />

            {calendarError ? (
                <section className="dashboard-calendar-fallback">
                    <ErrorState>No se pudo cargar el calendario de órdenes.</ErrorState>
                </section>
            ) : (
                <CalendarMonth
                    events={calendarEvents}
                    monthDate={monthDate}
                    onNextMonth={() => changeMonth(1)}
                    onPreviousMonth={() => changeMonth(-1)}
                />
            )}

            {(upcomingError || recentError) ? (
                <ErrorState>{upcomingError || recentError}</ErrorState>
            ) : null}

            <section className="dashboard-orders-grid">
                {loadingUpcoming ? (
                    <LoadingState>Cargando próximas órdenes...</LoadingState>
                ) : (
                    <UpcomingOrdersCard orders={upcomingOrders.slice(0, 5)} />
                )}

                {loadingRecent ? (
                    <LoadingState>Cargando órdenes recientes...</LoadingState>
                ) : (
                    <RecentOrdersCard orders={recentOrders} />
                )}
            </section>

            {summaryError ? <ErrorState>{summaryError}</ErrorState> : null}
            <BusinessStatusCards loading={loadingSummary} summary={businessSummary} />
            <DashboardQuickActions />
        </section>
    );
}

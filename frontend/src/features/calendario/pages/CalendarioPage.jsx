import { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { ErrorState } from "../../../shared/components/ErrorState";
import { LoadingState } from "../../../shared/components/LoadingState";
import { PageHeader } from "../../../shared/components/PageHeader";
import { useResourceList } from "../../../shared/hooks/useResourceList";
import { CalendarioOrdenes } from "../components/CalendarioOrdenes";
import { getMonthRange } from "../components/calendarUtils";
import { calendarioApi } from "../services/calendarioApi";

export function CalendarioPage() {
    const [monthDate, setMonthDate] = useState(() => new Date());
    const navigate = useNavigate();
    const params = useMemo(() => getMonthRange(monthDate), [monthDate]);
    const { error, items: events, loading } = useResourceList(calendarioApi.dashboard, params);
    const navigateToOrder = (event) => {
        if (event?.id) navigate(`/app/ordenes-trabajo/${event.id}`);
    };

    return (
        <section className="page">
            <PageHeader
                description="Visualiza las ordenes programadas y accede a su detalle desde el calendario."
                title="Calendario"
            />

            {loading ? <LoadingState>Cargando calendario...</LoadingState> : null}
            {error ? <ErrorState>{error}</ErrorState> : null}

            {!loading ? (
                <CalendarioOrdenes
                    events={events}
                    monthDate={monthDate}
                    onNextMonth={() => setMonthDate(new Date(monthDate.getFullYear(), monthDate.getMonth() + 1, 1))}
                    onPreviousMonth={() => setMonthDate(new Date(monthDate.getFullYear(), monthDate.getMonth() - 1, 1))}
                    onSelectOrden={navigateToOrder}
                />
            ) : null}
        </section>
    );
}

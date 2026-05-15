import { apiRequest, endpoints } from "../../../shared/api";

export const dashboardService = {
    resumen: () => apiRequest(endpoints.dashboard.resumen),
    calendario: (params) => apiRequest(endpoints.dashboardCalendario, { params }),
    proximasOrdenes: (params) =>
        apiRequest(endpoints.proximasOrdenes, { params }),
};

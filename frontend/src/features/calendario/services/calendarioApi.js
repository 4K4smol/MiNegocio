import { apiRequest, endpoints } from "../../../shared/api";

export const calendarioApi = {
    list: (params) => apiRequest(endpoints.calendarioEventos, { params }),
    dashboard: (params) => apiRequest(endpoints.dashboardCalendario, { params }),
};


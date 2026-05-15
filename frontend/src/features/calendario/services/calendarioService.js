import { apiRequest, createCrudApi, endpoints } from "../../../shared/api";

export const calendarioService = {
    ...createCrudApi(endpoints.calendarioEventos),
    dashboardCalendario: (params) =>
        apiRequest(endpoints.dashboardCalendario, { params }),
};

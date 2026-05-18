import { createCrudApi, endpoints } from "../../../shared/api";
import { calendarioApi } from "./calendarioApi";

export const calendarioService = {
    ...createCrudApi(endpoints.calendarioEventos),
    dashboardCalendario: calendarioApi.dashboard,
};

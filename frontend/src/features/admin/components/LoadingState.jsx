import { LoadingState as SharedLoadingState } from "../../../shared/components/LoadingState";

export function LoadingState({ children = "Cargando..." }) {
    return <SharedLoadingState className="admin-loading">{children}</SharedLoadingState>;
}

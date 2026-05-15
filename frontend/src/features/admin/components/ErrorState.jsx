import { ErrorState as SharedErrorState } from "../../../shared/components/ErrorState";

export function ErrorState({ children = "No se ha podido cargar la información." }) {
    return <SharedErrorState className="form-alert">{children}</SharedErrorState>;
}

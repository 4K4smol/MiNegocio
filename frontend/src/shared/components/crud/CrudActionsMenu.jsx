import { RowActionsMenu } from "../RowActionsMenu";

export function CrudActionsMenu({ actions = [] }) {
    return <RowActionsMenu actions={actions} />;
}

import { DataTable } from "../../../shared/components/DataTable";

export function AdminDataTable({ columns = [], children, empty }) {
    return (
        <DataTable
            cardClassName="admin-card admin-table-card"
            columns={columns}
            empty={empty}
            tableClassName="admin-table"
            wrapClassName="admin-table-wrap"
        >
            {children}
        </DataTable>
    );
}

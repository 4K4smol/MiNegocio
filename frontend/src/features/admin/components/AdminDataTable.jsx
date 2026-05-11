export function AdminDataTable({ columns = [], children, empty }) {
    if (empty) return empty;

    return (
        <section className="admin-card admin-table-card">
            <div className="admin-table-wrap">
                <table className="admin-table">
                    <thead>
                        <tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr>
                    </thead>
                    <tbody>{children}</tbody>
                </table>
            </div>
        </section>
    );
}

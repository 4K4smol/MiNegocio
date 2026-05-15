const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

const getColumnKey = (column) =>
    typeof column === "string" ? column : column.key || column.label;

const getColumnLabel = (column) =>
    typeof column === "string" ? column : column.label;

export function DataTable({
    columns = [],
    children,
    empty,
    className = "",
    cardClassName = "",
    wrapClassName = "",
    tableClassName = "",
}) {
    if (empty) return empty;

    return (
        <section className={joinClasses("table-card", cardClassName, className)}>
            <div className={joinClasses("table-wrap", wrapClassName)}>
                <table className={joinClasses("data-table", tableClassName)}>
                    {columns.length ? (
                        <thead>
                            <tr>
                                {columns.map((column) => (
                                    <th key={getColumnKey(column)} scope="col">
                                        {getColumnLabel(column)}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                    ) : null}
                    <tbody>{children}</tbody>
                </table>
            </div>
        </section>
    );
}

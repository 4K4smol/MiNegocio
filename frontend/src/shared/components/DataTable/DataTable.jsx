import { Children, cloneElement, Fragment, isValidElement } from "react";

const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

const getColumnKey = (column) =>
    typeof column === "string" ? column : column.key || column.label;

const getColumnLabel = (column) =>
    typeof column === "string" ? column : column.label;

const enhanceCell = (cell, columnLabels, index) => {
    if (!isValidElement(cell) || cell.type !== "td") return cell;
    if (cell.props["data-label"]) return cell;

    return cloneElement(cell, {
        "data-label": columnLabels[index] || "",
    });
};

const enhanceRow = (row, columnLabels) => {
    if (!isValidElement(row)) return row;

    if (row.type === Fragment) {
        return cloneElement(row, undefined, enhanceRows(row.props.children, columnLabels));
    }

    if (row.type !== "tr") return row;

    let cellIndex = 0;
    const enhancedCells = Children.map(row.props.children, (cell) => {
        if (!isValidElement(cell) || cell.type !== "td") return cell;
        const enhancedCell = enhanceCell(cell, columnLabels, cellIndex);
        cellIndex += 1;
        return enhancedCell;
    });

    return cloneElement(row, undefined, enhancedCells);
};

const enhanceRows = (children, columnLabels) =>
    Children.map(children, (row) => enhanceRow(row, columnLabels));

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

    const columnLabels = columns.map(getColumnLabel);

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
                    <tbody>{enhanceRows(children, columnLabels)}</tbody>
                </table>
            </div>
        </section>
    );
}

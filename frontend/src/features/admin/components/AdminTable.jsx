import { Children, cloneElement, Fragment, isValidElement } from "react";

const enhanceCell = (cell, columns, index) => {
    if (!isValidElement(cell) || cell.type !== "td" || cell.props["data-label"]) return cell;

    return cloneElement(cell, { "data-label": columns[index] || "" });
};

const enhanceRow = (row, columns) => {
    if (!isValidElement(row)) return row;

    if (row.type === Fragment) {
        return cloneElement(row, undefined, enhanceRows(row.props.children, columns));
    }

    if (row.type !== "tr") return row;

    let cellIndex = 0;
    const cells = Children.map(row.props.children, (cell) => {
        if (!isValidElement(cell) || cell.type !== "td") return cell;
        const enhancedCell = enhanceCell(cell, columns, cellIndex);
        cellIndex += 1;
        return enhancedCell;
    });

    return cloneElement(row, undefined, cells);
};

const enhanceRows = (children, columns) =>
    Children.map(children, (row) => enhanceRow(row, columns));

export function AdminTable({ columns = [], children }) {
    return (
        <div className="admin-table-wrap">
            <table className="admin-table">
                <thead>
                    <tr>
                        {columns.map((column) => (
                            <th key={column}>{column}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>{enhanceRows(children, columns)}</tbody>
            </table>
        </div>
    );
}

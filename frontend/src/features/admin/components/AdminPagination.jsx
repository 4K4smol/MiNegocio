import { Pagination } from "../../../shared/components/Pagination";

export function AdminPagination({ meta, onPageChange, disabled = false }) {
    return (
        <Pagination
            buttonClassName="admin-button admin-button-ghost"
            className="admin-pagination"
            disabled={disabled}
            meta={meta}
            onPageChange={onPageChange}
        />
    );
}

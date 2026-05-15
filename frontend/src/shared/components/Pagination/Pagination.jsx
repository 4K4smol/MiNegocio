export function Pagination({ buttonClassName = "button button-ghost", className = "", disabled = false, meta, onPageChange }) {
    if (!meta?.total || meta.total <= (meta.perPage || 0)) return null;

    const currentPage = meta.currentPage || 1;
    const lastPage = meta.lastPage || 1;

    return (
        <div className={["pagination", className].filter(Boolean).join(" ")}>
            <span>
                Total: <strong>{meta.total}</strong> · Página <strong>{currentPage}</strong> de{" "}
                <strong>{lastPage}</strong>
            </span>
            <div>
                <button
                    type="button"
                    className={buttonClassName}
                    disabled={disabled || currentPage <= 1}
                    onClick={() => onPageChange(currentPage - 1)}
                >
                    Anterior
                </button>
                <button
                    type="button"
                    className={buttonClassName}
                    disabled={disabled || currentPage >= lastPage}
                    onClick={() => onPageChange(currentPage + 1)}
                >
                    Siguiente
                </button>
            </div>
        </div>
    );
}

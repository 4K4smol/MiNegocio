import { useId } from "react";
import { Search } from "lucide-react";
import "./searchFilters.css";

const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function SearchFilters({
    ariaLabel = "Filtros de busqueda",
    children,
    className = "",
    loading = false,
    onReset,
    onSubmit,
}) {
    const handleSubmit = (event) => {
        event.preventDefault();
        onSubmit?.(event);
    };

    const handleReset = (event) => {
        event.preventDefault();
        onReset?.(event);
    };

    return (
        <form
            aria-label={ariaLabel}
            className={joinClasses("search-filters", className)}
            onSubmit={handleSubmit}
            onReset={handleReset}
        >
            <div className="search-filters__grid">{children}</div>
            <div className="search-filters__actions">
                <button className="search-filters__button search-filters__button--primary" disabled={loading} type="submit">
                    Buscar
                </button>
                <button className="search-filters__button" disabled={loading} type="reset">
                    Limpiar
                </button>
            </div>
        </form>
    );
}

export function SearchInput({
    disabled = false,
    label = "Buscar",
    name = "search",
    onChange,
    placeholder = "Buscar",
    value,
}) {
    const generatedId = useId();
    const id = `${name || "search"}-${generatedId}`;

    return (
        <label className="search-filters__field search-filters__field--search" htmlFor={id}>
            <span>{label}</span>
            <span className="search-filters__input-wrap">
                <Search aria-hidden="true" className="search-filters__icon" focusable="false" size={18} />
                <input
                    className="search-filters__input"
                    disabled={disabled}
                    id={id}
                    name={name}
                    placeholder={placeholder}
                    type="search"
                    value={value}
                    onChange={onChange}
                />
            </span>
        </label>
    );
}

export function FilterField({ children, label, wide = false }) {
    return (
        <label className={joinClasses("search-filters__field", wide && "search-filters__field--wide")}>
            {label ? <span>{label}</span> : null}
            {children}
        </label>
    );
}

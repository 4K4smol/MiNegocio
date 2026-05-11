export function Table({ className = "", ...props }) {
    return (
        <table
            className={["data-table", className].filter(Boolean).join(" ")}
            {...props}
        />
    );
}

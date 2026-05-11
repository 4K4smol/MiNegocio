export function PageHeader({ actions, eyebrow, title, children }) {
    return (
        <header className="page-header page-header-row">
            <div>
                {eyebrow ? <span className="eyebrow">{eyebrow}</span> : null}
                <h1>{title}</h1>
                {children ? <p>{children}</p> : null}
            </div>
            {actions ? <div className="page-actions">{actions}</div> : null}
        </header>
    );
}

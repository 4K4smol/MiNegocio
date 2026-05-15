const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function PageHeader({
    actions,
    actionsClassName = "page-actions",
    children,
    className = "",
    contentClassName = "",
    description,
    eyebrow,
    headingLevel = 1,
    title,
}) {
    const Heading = `h${headingLevel}`;

    return (
        <header className={joinClasses("page-header page-header-row", className)}>
            <div className={contentClassName}>
                {eyebrow ? <span className="eyebrow">{eyebrow}</span> : null}
                <Heading>{title}</Heading>
                {description || children ? <p>{description || children}</p> : null}
            </div>
            {actions ? <div className={actionsClassName}>{actions}</div> : null}
        </header>
    );
}

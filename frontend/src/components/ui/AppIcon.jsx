export function AppIcon({
    icon: Icon,
    size = 20,
    strokeWidth = 2,
    className = "",
    ...props
}) {
    if (!Icon) return null;

    return (
        <Icon
            aria-hidden="true"
            className={className}
            focusable="false"
            size={size}
            strokeWidth={strokeWidth}
            {...props}
        />
    );
}

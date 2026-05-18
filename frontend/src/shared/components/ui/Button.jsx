import { createElement } from "react";

export function Button({
    as = "button",
    className = "",
    variant = "primary",
    ...props
}) {
    const variantClass = variant === "secondary" ? "button-secondary" : "";
    const ghostClass = variant === "ghost" ? "button-ghost" : "";
    const dangerClass = variant === "danger" ? "button-danger" : "";

    return createElement(as, {
        className: ["button", variantClass, ghostClass, dangerClass, className]
            .filter(Boolean)
            .join(" "),
        ...props,
    });
}

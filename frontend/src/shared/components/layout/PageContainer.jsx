import { createElement } from "react";

export function PageContainer({ as = "section", className = "", ...props }) {
    return createElement(as, {
        className: ["page", className].filter(Boolean).join(" "),
        ...props,
    });
}

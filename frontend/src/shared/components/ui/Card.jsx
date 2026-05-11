import { createElement } from "react";

export function Card({ as = "section", className = "", ...props }) {
    return createElement(as, {
        className: ["card", className].filter(Boolean).join(" "),
        ...props,
    });
}

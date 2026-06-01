import { useEffect, useId, useRef, useState } from "react";
import { MoreVertical } from "lucide-react";
import { Link } from "react-router-dom";
import "./rowActionsMenu.css";

const getMenuPosition = (button) => {
    const rect = button.getBoundingClientRect();
    return {
        top: rect.bottom + 6,
        right: window.innerWidth - rect.right,
    };
};

export function RowActionsMenu({ actions = [] }) {
    const [isOpen, setIsOpen] = useState(false);
    const [position, setPosition] = useState({ top: 0, right: 0 });
    const buttonRef = useRef(null);
    const menuRef = useRef(null);
    const menuId = useId();
    const visibleActions = actions.filter((action) => !action.hidden);

    useEffect(() => {
        if (!isOpen) return undefined;

        const handlePointerDown = (event) => {
            if (buttonRef.current?.contains(event.target) || menuRef.current?.contains(event.target)) return;
            setIsOpen(false);
        };

        const handleKeyDown = (event) => {
            if (event.key === "Escape") setIsOpen(false);
        };

        const handleReposition = () => {
            if (buttonRef.current) setPosition(getMenuPosition(buttonRef.current));
        };

        document.addEventListener("pointerdown", handlePointerDown);
        document.addEventListener("keydown", handleKeyDown);
        window.addEventListener("resize", handleReposition);
        window.addEventListener("scroll", handleReposition, true);

        return () => {
            document.removeEventListener("pointerdown", handlePointerDown);
            document.removeEventListener("keydown", handleKeyDown);
            window.removeEventListener("resize", handleReposition);
            window.removeEventListener("scroll", handleReposition, true);
        };
    }, [isOpen]);

    const toggleMenu = () => {
        if (!isOpen && buttonRef.current) setPosition(getMenuPosition(buttonRef.current));
        setIsOpen((current) => !current);
    };

    const runAction = (action) => {
        if (action.disabled) return false;
        if (action.confirm && !window.confirm(action.confirmMessage || "Confirma esta acción.")) return false;
        action.onClick?.();
        setIsOpen(false);
        return true;
    };

    if (!visibleActions.length) return null;

    return (
        <div className="row-actions">
            <button
                ref={buttonRef}
                aria-controls={isOpen ? menuId : undefined}
                aria-expanded={isOpen}
                aria-haspopup="menu"
                aria-label="Abrir acciones"
                className={`row-actions__button ${isOpen ? "is-open" : ""}`}
                type="button"
                onClick={toggleMenu}
            >
                <MoreVertical aria-hidden="true" className="row-actions__icon" size={18} />
            </button>
            {isOpen ? (
                <div
                    ref={menuRef}
                    className="row-actions__menu"
                    id={menuId}
                    role="menu"
                    style={{ top: position.top, right: position.right }}
                >
                    {visibleActions.map((action, index) => {
                        const className = [
                            "row-actions__item",
                            action.variant ? `row-actions__item--${action.variant}` : "",
                        ].filter(Boolean).join(" ");
                        const content = (
                            <>
                                {action.icon ? <span className="row-actions__icon">{action.icon}</span> : null}
                                <span>{action.label}</span>
                            </>
                        );

                        if (action.to && !action.disabled) {
                            return (
                                <Link
                                    className={className}
                                    key={`${action.label}-${index}`}
                                    role="menuitem"
                                    to={action.to}
                                    onClick={(event) => {
                                        if (!runAction(action)) event.preventDefault();
                                    }}
                                >
                                    {content}
                                </Link>
                            );
                        }

                        return (
                            <button
                                className={className}
                                disabled={action.disabled}
                                key={`${action.label}-${index}`}
                                role="menuitem"
                                type="button"
                                onClick={() => runAction(action)}
                            >
                                {content}
                            </button>
                        );
                    })}
                </div>
            ) : null}
        </div>
    );
}

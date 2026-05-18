const joinClasses = (...classes) => classes.filter(Boolean).join(" ");

export function Stepper({ className, currentStep, steps }) {
    return (
        <div className={joinClasses("stepper", className)}>
            {steps.map((step, index) => {
                const stepNumber = index + 1;
                const isDone = stepNumber < currentStep;
                const isActive = stepNumber === currentStep;
                const isLast = index === steps.length - 1;

                return (
                    <div
                        key={index}
                        style={{
                            alignItems: "center",
                            display: "contents",
                        }}
                    >
                        <div className="stepper__step">
                            <div className={joinClasses("stepper__bubble", isDone ? "is-done" : "", isActive ? "is-active" : "")}>
                                {isDone ? "✓" : stepNumber}
                            </div>
                            <span className={joinClasses("stepper__label", isActive ? "is-active" : "")}>
                                {step.label}
                            </span>
                        </div>
                        {!isLast && (
                            <div className={joinClasses("stepper__connector", isDone ? "is-done" : "")} />
                        )}
                    </div>
                );
            })}
        </div>
    );
}

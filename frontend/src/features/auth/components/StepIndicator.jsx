export function StepIndicator({ currentStep, steps }) {
    return (
        <ol className="step-indicator" aria-label="Progreso del registro">
            {steps.map((step, index) => {
                const stepNumber = index + 1;
                const isActive = stepNumber === currentStep;
                const isDone = stepNumber < currentStep;

                return (
                    <li
                        className={[
                            "step-item",
                            isActive ? "step-active" : "",
                            isDone ? "step-done" : "",
                        ]
                            .filter(Boolean)
                            .join(" ")}
                        key={step}
                    >
                        <span>{stepNumber}</span>
                        <strong>{step}</strong>
                    </li>
                );
            })}
        </ol>
    );
}

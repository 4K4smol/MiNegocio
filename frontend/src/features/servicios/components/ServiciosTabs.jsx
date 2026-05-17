const TABS = [
    { id: "servicios", label: "Servicios" },
    { id: "categorias", label: "Categorias" },
    { id: "tarifas", label: "Tipos de tarifa" },
];

export function ServiciosTabs({ activeTab, onChange }) {
    return (
        <nav aria-label="Apartados de servicios" className="module-tabs">
            {TABS.map((tab) => (
                <button
                    aria-current={activeTab === tab.id ? "page" : undefined}
                    className={activeTab === tab.id ? "is-active" : ""}
                    key={tab.id}
                    type="button"
                    onClick={() => onChange(tab.id)}
                >
                    {tab.label}
                </button>
            ))}
        </nav>
    );
}

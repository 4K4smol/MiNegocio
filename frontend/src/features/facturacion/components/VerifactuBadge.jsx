const VERIFACTU_ESTADOS = {
    no_configurado: { label: 'No configurado', color: '#6b7280', bg: '#f3f4f6' },
    pendiente: { label: 'Pendiente de envío', color: '#d97706', bg: '#fef3c7' },
    enviando: { label: 'Enviando...', color: '#2563eb', bg: '#dbeafe' },
    aceptado: { label: 'Aceptado', color: '#059669', bg: '#d1fae5' },
    rechazado: { label: 'Rechazado', color: '#dc2626', bg: '#fee2e2' },
    error_tecnico: { label: 'Error técnico', color: '#9333ea', bg: '#f3e8ff' },
    anulado: { label: 'Anulado', color: '#6b7280', bg: '#f3f4f6' },
}

export function VerifactuBadge({ estado }) {
    const config = VERIFACTU_ESTADOS[estado] || VERIFACTU_ESTADOS.no_configurado
    return (
        <span
            style={{
                display: 'inline-flex',
                alignItems: 'center',
                padding: '0.25rem 0.75rem',
                borderRadius: '9999px',
                fontSize: '0.8125rem',
                fontWeight: 600,
                color: config.color,
                backgroundColor: config.bg,
            }}
        >
            {config.label}
        </span>
    )
}

export const formatDate = (value) => {
    if (!value) return "Sin fecha";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "Sin fecha";

    return new Intl.DateTimeFormat("es-ES").format(date);
};

export const formatCurrency = (value) =>
    new Intl.NumberFormat("es-ES", {
        currency: "EUR",
        style: "currency",
    }).format(Number(value || 0));

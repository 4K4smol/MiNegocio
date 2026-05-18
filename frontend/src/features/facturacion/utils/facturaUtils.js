export const getEstadoFactura = (factura) => {
    if (!factura) return ''
    if (factura.pagada) return 'pagada'
    return (
        factura.estado_codigo ||
        factura.estado_factura?.codigo ||
        (typeof factura.estado_factura === 'string' ? factura.estado_factura : '') ||
        ''
    )
}

export const isFacturaBorrador = (f) => getEstadoFactura(f) === 'borrador'
export const isFacturaEmitida = (f) => getEstadoFactura(f) === 'emitida'
export const isFacturaPagada = (f) => Boolean(f?.pagada) || getEstadoFactura(f) === 'pagada'
export const isFacturaAnulada = (f) => getEstadoFactura(f) === 'anulada'
export const isFacturaRectificada = (f) => getEstadoFactura(f) === 'rectificada'
export const isFacturaEditable = (f) => isFacturaBorrador(f)
export const isFacturaFinalizada = (f) => isFacturaAnulada(f) || isFacturaRectificada(f)

export const getNumeroFactura = (factura) => {
    if (!factura) return ''
    const serie = factura.serie ? `${factura.serie}-` : ''
    return `${serie}${factura.numero || factura.id || ''}`
}

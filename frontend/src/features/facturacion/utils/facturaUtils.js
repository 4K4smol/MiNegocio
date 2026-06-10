export const getRawEstadoFactura = (factura) => {
    if (!factura) return ''

    return (
        factura.estado_codigo ||
        factura.estado_factura?.codigo ||
        (typeof factura.estado_factura === 'string' ? factura.estado_factura : '') ||
        ''
    )
}

export const getEstadoFactura = (factura) => {
    if (!factura) return ''

    const estado = getRawEstadoFactura(factura)

    if (estado === 'anulada') return 'anulada'
    if (estado === 'rectificada') return 'rectificada'
    if (estado === 'borrador') return 'borrador'

    if (isFacturaAbonada(factura)) return 'abonada'
    if (isFacturaPagada(factura)) return 'pagada'

    return estado || ''
}

export const isFacturaBorrador = (f) => getRawEstadoFactura(f) === 'borrador'
export const isFacturaEmitida = (f) => getRawEstadoFactura(f) === 'emitida'
export const isFacturaPagada = (f) => Boolean(f?.pagada) || getRawEstadoFactura(f) === 'pagada'
export const isFacturaRectificativa = (f) => f?.tipo_factura === 'rectificativa'
export const isFacturaNegativa = (f) => Number(f?.total || 0) < 0
export const isFacturaAbonada = (f) => isFacturaPagada(f) && isFacturaRectificativa(f) && isFacturaNegativa(f)
export const isFacturaAnulada = (f) => getRawEstadoFactura(f) === 'anulada'
export const isFacturaRectificada = (f) => getRawEstadoFactura(f) === 'rectificada'
export const isFacturaEditable = (f) => isFacturaBorrador(f)
export const isFacturaFinalizada = (f) => isFacturaAnulada(f) || isFacturaRectificada(f)

export const puedeMarcarComoPagada = (f) =>
    Number(f?.total || 0) > 0 &&
    isFacturaEmitida(f) &&
    !isFacturaPagada(f) &&
    !isFacturaFinalizada(f)

export const puedeRegistrarDevolucion = (f) =>
    isFacturaRectificativa(f) &&
    isFacturaNegativa(f) &&
    isFacturaEmitida(f) &&
    !isFacturaPagada(f) &&
    !isFacturaFinalizada(f)

export const puedeAnularFactura = (f) =>
    !isFacturaBorrador(f) &&
    !isFacturaFinalizada(f) &&
    !isFacturaRectificativa(f) &&
    Number(f?.total || 0) > 0 &&
    (isFacturaEmitida(f) || isFacturaPagada(f))

export const puedeRectificarFactura = (f) =>
    !isFacturaBorrador(f) &&
    !isFacturaFinalizada(f) &&
    !isFacturaRectificativa(f) &&
    (isFacturaEmitida(f) || isFacturaPagada(f))

export const getNumeroFactura = (factura) => {
    if (!factura) return ''
    const serie = factura.serie ? `${factura.serie}-` : ''
    return `${serie}${factura.numero || factura.id || ''}`
}

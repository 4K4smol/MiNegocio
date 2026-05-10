# VeriFactu / RRSIF

MiNegocio implementa una arquitectura preparada para VeriFactu, manteniendo en esta version solo remision simulada.

## Estado actual

- El sistema genera registros de facturacion asociados a facturas emitidas y anulaciones.
- Los registros se encadenan mediante hash para preservar integridad y trazabilidad.
- El flujo registra eventos de auditoria para altas, anulaciones e intentos de remision.
- La remision disponible es simulada y no realiza envio real a AEAT.
- No existe comunicacion real con AEAT en esta version.

## Arquitectura

La remision queda desacoplada mediante clientes intercambiables:

- `App\Services\Verifactu\VerifactuClientInterface`
- `App\Services\Verifactu\VerifactuSimuladoClient`
- `App\Services\Verifactu\VerifactuRealClient`
- `App\Services\VerifactuRemisionService`

`VerifactuRemisionService` actua como orquestador y selecciona el cliente segun `services.verifactu.modo`.

## Variables de entorno

```env
VERIFACTU_MODO=simulado
VERIFACTU_REMISION_REAL=false
VERIFACTU_ENDPOINT=
VERIFACTU_CERT_PATH=
VERIFACTU_CERT_PASSWORD=
VERIFACTU_TIMEOUT=30
```

## Modo simulado

El modo `simulado` busca registros pendientes, registra un intento simulado, marca los registros como `enviado_simulado` y conserva una respuesta interna en `respuesta_aeat`.

Este modo no envia datos reales ni conecta con AEAT.

## Modo real

El modo `real` no esta implementado todavia. Si no existe configuracion completa de endpoint y certificado, devuelve un error controlado y no modifica registros de facturacion.

## Flujo de prueba

1. Completar una orden de trabajo.
2. Generar factura desde la orden completada.
3. Validar la cadena de registros de facturacion.
4. Enviar pendientes en modo simulado con `POST /api/v1/verifactu/enviar-pendientes`.

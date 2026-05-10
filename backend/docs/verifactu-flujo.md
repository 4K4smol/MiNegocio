# Flujo de facturacion y VeriFactu simulado

Este proyecto implementa un flujo conceptual de facturacion con trazabilidad inspirada en VeriFactu/RRSIF. No sustituye una certificacion legal ni una integracion real con AEAT.

## Flujo actual

1. Una orden de trabajo se crea y se completa.
2. Desde una orden completada se genera una factura con lineas, impuestos y snapshot fiscal basico.
3. La factura genera un registro de facturacion de alta.
4. Si la factura se anula, se conserva la factura y se genera un registro de facturacion de anulacion.
5. Cada registro se encadena por empresa mediante el hash del registro anterior.
6. Los eventos de facturacion registran hitos operativos: factura generada, factura anulada, registro de alta, registro de anulacion y remision simulada.
7. La validacion de cadena comprueba primer registro, enlace con el registro anterior y hash recalculado.

## Partes reales dentro del sistema

- Persistencia de facturas, lineas e impuestos.
- Registros de facturacion de alta y anulacion.
- Encadenamiento hash por `empresa_id`.
- Conservacion basica de registros: los registros fiscales y eventos no se pueden borrar desde los modelos.
- Eventos de trazabilidad asociados a empresa, usuario, factura y registro.
- Estados internos de remision simulada: `pendiente`, `enviado_simulado` y `error_simulado`.

## Partes simuladas o conceptuales

- La remision AEAT es simulada. No hay conexion real con AEAT, ni endpoint externo, ni certificado digital.
- La respuesta AEAT guardada en `respuesta_aeat` es una respuesta interna simulada.
- El XML generado es una estructura interna conceptual para trazabilidad y pruebas; no debe considerarse el esquema oficial final de AEAT.
- No se realiza firma electronica real del XML ni validacion XSD oficial.

## Faltaria para produccion

- Certificado digital y custodia segura de claves.
- Firma electronica real.
- Esquema XML oficial AEAT aplicable y versionado.
- Endpoint real AEAT y gestion de comunicaciones.
- Validacion XSD.
- Gestion completa de respuestas reales, errores, reintentos y estados oficiales.
- Declaracion responsable del software.
- Pruebas legales y tecnicas completas con asesoramiento especializado.

# VERIFACTU simulado y catalogo minimo

Este documento sirve como guia para Codex cuando tenga que razonar sobre el
modulo de facturacion de MiNegocio con enfoque VERIFACTU simulado.

El objetivo es ensenar el funcionamiento profesional minimo: facturas fiscales,
registros tecnicos, cadena hash, eventos y remision simulada. No describe una
integracion real con la Agencia Tributaria ni sustituye una revision legal,
tecnica o fiscal para produccion.

## Principio de trabajo

Para desarrollo y demostracion, el unico modo valido es `simulado`.

En este modo el sistema puede:

- Generar registros de facturacion asociados a facturas fiscales.
- Crear registros de `alta` y `anulacion`.
- Encadenar registros por empresa mediante hash.
- Registrar eventos de trazabilidad.
- Marcar registros pendientes como `enviado_simulado`.
- Guardar una respuesta interna ficticia en `respuesta_aeat`.

En este modo el sistema no debe:

- Conectar con AEAT.
- Usar endpoint real.
- Usar certificado digital.
- Firmar XML real.
- Validar contra XSD oficial.
- Presentarse como software listo para produccion legal.

Si Codex necesita explicar o ampliar el flujo, debe conservar siempre esta
frontera: la simulacion ensena el mecanismo, pero no realiza una remision real.

## Catalogo minimo de tipos de factura

Para una demostracion clara, suficiente y profesional, usar estos tipos:

| Codigo | Uso didactico | Registro VERIFACTU |
| --- | --- | --- |
| `proforma` | Documento no fiscal para ensenar el limite entre documento comercial y factura fiscal. | No genera registro. |
| `ordinaria` | Factura fiscal estandar. | Genera registro de `alta`. |
| `simplificada` | Factura fiscal basica cuando el caso de uso no requiere factura completa. | Genera registro de `alta`. |
| `rectificativa` | Corrige una factura fiscal ya emitida. | Genera registro de `alta`. |

Codex debe tratar `presupuesto` y `recapitulativa` como tipos fuera del minimo
docente. Pueden existir en el codigo actual por compatibilidad o evolucion
futura, pero no son necesarios para explicar el flujo base.

La regla practica es:

- `proforma` no entra en la cadena de registros.
- `ordinaria`, `simplificada` y `rectificativa` si entran en la cadena.
- Una factura rectificativa no requiere un tipo de registro propio: es una
  factura de tipo `rectificativa` que genera un registro tecnico de `alta`.

## Catalogo minimo de tipos de registro

El minimo profesional de registros de facturacion es:

| Codigo | Cuando se crea | Que representa |
| --- | --- | --- |
| `alta` | Al emitir una factura fiscal. | La incorporacion de una factura a la cadena de registros. |
| `anulacion` | Al anular una factura fiscal que ya tenia registro de alta. | La baja tecnica del alta previa, sin borrar ni modificar el registro original. |

No crear tipos adicionales para explicar rectificaciones, cobros, pagos,
presupuestos o proformas. Esos conceptos pertenecen a la factura, al estado
comercial o al historial, no al tipo tecnico de registro.

## Escenarios base

### Proforma emitida

Una `proforma` es un documento no fiscal. Puede tener lineas e importes, pero no
debe generar numero fiscal definitivo ni registro de facturacion.

Resultado esperado:

- Estado comercial de documento emitido o enviado.
- Sin registro `alta`.
- Sin hash ni enlace en cadena VERIFACTU.

### Factura ordinaria emitida

Una `ordinaria` representa el caso fiscal principal.

Resultado esperado:

- Se asigna serie y numero fiscal.
- Se crea registro de `alta`.
- Se calcula hash del payload fiscal.
- Se enlaza con el registro anterior de la misma empresa si existe.
- Queda pendiente de remision simulada.

### Factura simplificada emitida

Una `simplificada` funciona igual que una ordinaria a nivel de registro minimo.
La diferencia pertenece al tipo de factura, no al tipo de registro.

Resultado esperado:

- Se crea registro de `alta`.
- Entra en la misma cadena hash de la empresa.
- Queda pendiente de remision simulada.

### Factura emitida por error

Si una factura fiscal ya genero registro de `alta`, no se debe alterar ni borrar
ese registro. Para anularla, se crea un registro de `anulacion` vinculado al
alta original.

Resultado esperado:

- La factura y su alta original se conservan.
- Se crea registro de `anulacion`.
- La anulacion tambien entra en la cadena hash.
- Si procede emitir factura correcta, se emite una nueva factura con su propia
  alta.

### Factura rectificativa

Una `rectificativa` corrige una factura fiscal previa. A nivel tecnico minimo,
se trata como nueva factura fiscal y genera registro de `alta`.

Resultado esperado:

- La rectificativa referencia la factura original.
- Se crea una nueva factura fiscal.
- Se crea registro de `alta` para la rectificativa.
- La factura original puede quedar marcada como rectificada segun el flujo
  interno.

### Envio pendiente simulado

El envio de pendientes en modo `simulado` no remite datos a AEAT. Solo procesa
registros internos pendientes para probar el flujo operativo.

Resultado esperado:

- Registros pendientes pasan a `enviado_simulado`.
- Se registra un evento de remision simulada.
- Se conserva una respuesta interna ficticia.
- No hay endpoint externo, certificado ni comunicacion real.

## Guia para Codex

Cuando Codex trabaje en este modulo, debe preferir estas decisiones:

- Mantener separado el tipo de factura del tipo de registro.
- No inventar un registro `rectificativa`; usar factura `rectificativa` +
  registro `alta`.
- No hacer que `proforma` genere registros fiscales.
- No modificar registros ya generados para corregir errores; crear nuevos
  registros de anulacion o nuevas altas segun corresponda.
- No presentar el XML interno, QR interno o respuesta simulada como formato
  oficial AEAT.
- No activar ni sugerir remision real sin una tarea explicita de integracion
  legal y tecnica.

Para contexto del repo, revisar tambien:

- `backend/docs/verifactu.md`
- `backend/docs/verifactu-flujo.md`
- `backend/docs/declaracion-responsable-software.md`

## Referencias oficiales

- Pagina general AEAT sobre Sistemas Informaticos de Facturacion y VERI*FACTU:
  https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html
- Modalidades VERI*FACTU y NO VERI*FACTU:
  https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/cuestiones-generales/modalidades-cumplimiento-obligaciones.html
- Preguntas frecuentes sobre registros de facturacion de alta:
  https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/preguntas-frecuentes/registros-facturacion-alta.html
- Preguntas frecuentes sobre registros de facturacion de anulacion:
  https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/preguntas-frecuentes/registros-facturacion-anulacion.html
- Preguntas frecuentes sobre procedimientos de facturacion y rectificativas:
  https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/preguntas-frecuentes/procedimientos-facturacion.html

## Limites asumidos

- Esta guia no cambia seeders, modelos, servicios ni pruebas.
- Esta guia recomienda un minimo docente, aunque el codigo actual pueda tener
  mas tipos por compatibilidad.
- Esta guia no define cumplimiento legal completo.
- Esta guia no autoriza conexion real con sistemas de gobierno.

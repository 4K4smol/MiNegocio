# Declaracion responsable de software

Este modulo guarda una declaracion responsable interna y simple asociada al sistema informatico de facturacion y a una version concreta del software.

Su objetivo es dejar constancia tecnica de que MiNegocio mantiene un flujo de facturacion con trazabilidad, conservacion de registros, encadenamiento hash y remision VeriFactu simulada.

## Alcance

- La declaracion se guarda en `declaraciones_responsables_software`.
- La declaracion es global del software, no de una empresa cliente.
- Cada version se identifica por `codigo_software` y `version_software`.
- Se calcula un `hash_declaracion` SHA-256 sobre los datos basicos del documento.
- No se permite borrar declaraciones desde el modelo.

## Importante

Este documento no es una declaracion legal completa ni sustituye la revision juridica. Para produccion faltarian, como minimo, validacion legal del texto, firma si procede, versionado formal del software, evidencias tecnicas completas y contraste con la normativa aplicable.

## Endpoints

- `GET /api/v1/declaraciones-responsables-software`
- `GET /api/v1/declaraciones-responsables-software/{id}`
- `POST /api/v1/declaraciones-responsables-software`

El `POST` puede recibir campos opcionales como `nombre_software`, `codigo_software`, `version_software`, `productor_nombre`, `productor_nif`, `productor_direccion`, `descripcion`, `componentes`, `modo_verifactu`, `permite_multiples_obligados`, `fecha_declaracion`, `lugar_declaracion`, `texto_declaracion`, `pdf_path`, `activa` y `metadatos`. Si no se envia texto, el servicio genera uno basico.

# Backend MiNegocio

API Laravel para la gestion de MiNegocio: clientes, empresas, ordenes de trabajo, facturacion, inventario, calendario y administracion.

## Requisitos

- PHP 8.3 o superior
- Composer
- Base de datos compatible con la configuracion de Laravel

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Configura en `.env` la conexion de base de datos, el dominio del frontend y las credenciales necesarias para los servicios externos antes de arrancar el servidor.

## Desarrollo

```bash
php artisan serve
```

Para ejecutar la cola y otros procesos auxiliares, revisa los scripts disponibles en `composer.json`.

## Tests

```bash
php artisan test
```

## Estructura Principal

- `app/Http/Controllers/Api/V1`: controladores de la API.
- `app/Services`: logica de negocio.
- `database/migrations`: estructura de base de datos.
- `database/seeders`: datos iniciales y catalogos.
- `tests`: pruebas automatizadas.

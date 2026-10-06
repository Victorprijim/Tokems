# User API (Laravel)

Esta es mi práctica de DAW: una API REST en Laravel para gestionar usuarios (modelo `User`) con base de datos SQLite.

## Qué hace

| Acción | Método | Ruta | Body |
|--------|--------|------|------|
| get | GET | `/api/users` | — (paginación de 10 en 10, `?page=2`...) |
| create | POST | `/api/users/create` | `username`, `email`, `password` |
| login | POST | `/api/users/login` | `email`, `password` |
| update_username | POST | `/api/users/update_username` | `email`, `password`, `username` |
| update_email | POST | `/api/users/update_email` | `email`, `password`, `new_email` |
| update_password | POST | `/api/users/update_password` | `email`, `password`, `new_password` |
| delete | POST | `/api/users/delete` | `email`, `password` |

Los updates y el delete piden el email y la contraseña del usuario:

- Si faltan datos o no son válidos (email repetido, contraseña de menos de 6 caracteres...) la API devuelve **422** con los errores.
- Si el email no existe o la contraseña es incorrecta devuelve **401** con `{"message": "Las credenciales introducidas no son correctas."}`.

## Cómo lo pongo en marcha

Necesito PHP 8.3 o superior y Composer.

```bash
composer install
cp .env.example .env        # en Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed  # si pregunta, digo que sí a crear database/database.sqlite
php artisan serve
```

La API queda en `http://127.0.0.1:8000/api` (si el puerto 8000 está ocupado uso `php artisan serve --port=8002`).

El seeder crea un usuario de prueba (`test@example.com` / `password`) y 12 usuarios más.

## Tests

```bash
php artisan test
```

## Postman

Importo `postman/User_API.postman_collection.json` en Postman. La variable `base_url` vale `http://127.0.0.1:8000/api` (la cambio si uso otro puerto).

La colección se puede ejecutar entera con **Run collection** las veces que quiera: cada vez crea un usuario nuevo con un email único, hace login, cambia su username, email y contraseña (las siguientes peticiones usan ya los datos nuevos), prueba una contraseña incorrecta (401) y al final lo borra. No toca los usuarios del seeder.

También se puede lanzar desde la terminal con Newman:

```bash
npx newman run postman/User_API.postman_collection.json --env-var base_url=http://127.0.0.1:8000/api
```

# API User + Token (Laravel)

API sencilla con los modelos **User** y **Token**. Todas las respuestas tienen el formato:

```json
{ "success": true, "message": "...", "data": ... }
```

## Archivos

| Archivo | Qué hace |
|---|---|
| `app/Http/Controllers/UserController.php` | `index`, `store` (y `create` como alias), `login`, `updateName` |
| `app/Models/Token.php` | Modelo Token (`user_id`, `token`) con relación `belongsTo(User)` |
| `app/Models/User.php` | `password` oculto en el JSON y relación `hasOne(Token)` |
| `database/migrations/2026_10_01_000000_create_tokens_table.php` | Tabla `tokens` (FK a users con cascade, token único) |
| `routes/api.php` | Las 4 rutas de la API |
| `API_User_Token.postman_collection.json` | Colección de Postman para probarlo |

> **User.php:** si ya tienes cambios en tu modelo User, no lo sustituyas entero: basta con que
> `password` esté en `$hidden` (o en `#[Hidden([...])]` en Laravel 13) y añadir el método `token()`.

## Integración con Laravel Herd

Con Herd el proyecto se sirve solo en **http://&lt;nombre-carpeta&gt;.test** (no hace falta `php artisan serve`).
Por ejemplo, la carpeta `api-user-token` dentro de la carpeta de sitios de Herd → `http://api-user-token.test`.

1. Copia los archivos de este paquete en tu proyecto respetando las carpetas.
2. Abre una terminal **en la carpeta del proyecto** (en Herd: botón *Open in terminal*, o `cd` a la carpeta) y ejecuta:

```bash
# Laravel 11 o superior: crea routes/api.php y lo registra (prefijo /api)
php artisan install:api
```

   Si te pregunta si quieres sobrescribir/crear `routes/api.php`, después vuelve a copiar el `routes/api.php` de este paquete.

3. Base de datos (en el archivo `.env`):
   - **SQLite** (la que trae Laravel por defecto): `DB_CONNECTION=sqlite` y listo (usa `database/database.sqlite`).
   - **MySQL**: por ejemplo
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=api_user_token
     DB_USERNAME=root
     DB_PASSWORD=
     ```
     (crea antes la base de datos `api_user_token`).

4. Ejecuta las migraciones:

```bash
php artisan migrate
php artisan route:list --path=api   # comprobar que salen las 4 rutas
```

## Rutas

| Método | URL | Body (JSON) | Respuesta OK |
|---|---|---|---|
| GET | `/api/users` | — | 200, 10 primeros usuarios (sin password) |
| POST | `/api/users` | `name`, `email`, `password` (mín. 8) | 201, usuario creado |
| POST | `/api/login` | `email`, `password` | 200, `data` = token |
| PUT | `/api/users/name` | `token`, `name` (o cabecera `Authorization: Bearer <token>`) | 200, usuario actualizado |

Errores: 422 (validación, `data` = errores), 401 (login incorrecto o token no válido), 500 (error inesperado).

## Postman

1. *Import* → `API_User_Token.postman_collection.json`.
2. En la colección → pestaña **Variables** → cambia `base_url` a `http://<nombre-de-tu-carpeta>.test/api`
   (por defecto `http://api-user-token.test/api`; con `php artisan serve` sería `http://127.0.0.1:8000/api`).
3. Orden: **Crear usuario** → **Login** (guarda el token solo en la variable `token`) → **Cambiar nombre**.

Envía siempre la cabecera `Accept: application/json` (la colección ya la lleva).

## Prueba rápida con curl

```bash
curl -X POST http://api-user-token.test/api/users -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"name\":\"Victor\",\"email\":\"victor@example.com\",\"password\":\"secreto123\"}"
curl -X POST http://api-user-token.test/api/login -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"email\":\"victor@example.com\",\"password\":\"secreto123\"}"
curl -X PUT http://api-user-token.test/api/users/name -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"token\":\"TOKEN_DEL_LOGIN\",\"name\":\"Nuevo nombre\"}"
curl http://api-user-token.test/api/users -H "Accept: application/json"
```

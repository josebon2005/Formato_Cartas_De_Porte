# Despliegue en linea

Antes de publicar el sistema, crea el usuario administrador:

```bash
php artisan admin:create admin@tuempresa.com --name="Administrador"
```

## Variables de produccion

En el servidor, configura el archivo `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_base_datos
DB_PASSWORD=password_base_datos
```

## Comandos de despliegue

Ejecuta estos comandos en el servidor:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Documento raiz

El dominio debe apuntar a la carpeta:

```text
public
```

No debe apuntar a la raiz completa del proyecto.

## Seguridad

- Activa HTTPS/SSL.
- Usa un password fuerte para el administrador.
- Haz backups periodicos de la base de datos.
- Mantén `APP_DEBUG=false` en produccion.

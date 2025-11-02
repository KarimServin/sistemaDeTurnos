# Guía de Seguridad - Protección de Base de Datos

## 🔒 Protecciones Implementadas

### 1. Archivos de Configuración Protegidos

✅ **Bloqueo de acceso directo a `app/config/config.php`**
- Creado `.htaccess` en `app/` que bloquea todos los archivos PHP
- Creado `.htaccess` en `app/config/` con protección adicional
- **Resultado**: No se puede acceder directamente a `http://localhost/sistemaDeTurnos/app/config/config.php`

### 2. Manejo Seguro de Errores de BD

✅ **Errores no exponen información sensible en producción**
- En desarrollo: muestra detalles útiles para debugging
- En producción: mensaje genérico "Error de conexión a la base de datos"
- Detalles completos solo en logs del servidor (seguro)

### 3. Variables de Entorno

**Recomendación para producción**: Mover credenciales a variables de entorno o archivo fuera del webroot.

## 🛡️ Cómo Verificar que Está Protegido

### Prueba 1: Intentar acceder directamente al config
```
http://localhost/sistemaDeTurnos/app/config/config.php
```
**Resultado esperado**: Error 403 Forbidden

### Prueba 2: Verificar .htaccess
Los archivos `.htaccess` deben estar en:
- `app/.htaccess` ✅
- `app/config/.htaccess` ✅

## 📋 Configuración Recomendada para Producción

### 1. Cambiar Modo de Desarrollo
En `app/config/config.php`:
```php
define('MODO_DESARROLLO', false);
```

### 2. Usar Usuario MySQL Dedicado (Recomendado)
No uses `root` en producción. Crea un usuario específico:
```sql
CREATE USER 'app_turnos'@'localhost' IDENTIFIED BY 'contraseña_fuerte';
GRANT SELECT, INSERT, UPDATE, DELETE ON sistema_turnos.* TO 'app_turnos'@'localhost';
FLUSH PRIVILEGES;
```

Luego en `config.php`:
```php
define('DB_USER', 'app_turnos');
define('DB_PASS', 'contraseña_fuerte');
```

### 3. Cambiar Permisos del Archivo config.php (Opcional)
```bash
chmod 600 app/config/config.php  # Solo lectura/escritura para el dueño
```

### 4. Usar Variables de Entorno (Mejor Práctica)
Mover credenciales a `.env` fuera del webroot y cargarlas con:
```php
// Solo ejemplo - implementar con una librería como vlucas/phpdotenv
define('DB_PASS', getenv('DB_PASSWORD') ?: '');
```

## ⚠️ Advertencias Importantes

1. **Nunca subas `app/config/config.php` a Git con credenciales reales**
   - Usa `.gitignore` para excluirlo
   - Crea un `config.php.example` con valores de ejemplo

2. **No uses la contraseña del usuario root de MySQL**
   - Crea un usuario específico para la aplicación

3. **Verifica que Apache/PHP tenga permisos mínimos necesarios**
   - El usuario del servidor web no debería poder leer archivos fuera de `public/`

## ✅ Checklist de Seguridad

- [x] `.htaccess` protege `app/` y `app/config/`
- [x] Errores no exponen información en producción
- [x] Credenciales están fuera de `public/`
- [ ] Usuario MySQL dedicado (pendiente configurar)
- [ ] Variables de entorno implementadas (opcional pero recomendado)
- [ ] `config.php` en `.gitignore` (revisar)

## 🔍 Monitoreo

Los errores de conexión se registran en los logs de PHP/MySQL:
- Revisar periódicamente los logs para detectar intentos de acceso
- Configurar alertas para múltiples fallos de conexión


# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

## [1.0.0] - 2024-01

### Añadido
- Sistema completo de gestión de turnos con arquitectura MVC
- API RESTful para operaciones CRUD
- Validación de horarios disponibles
- Filtros avanzados (fecha, estado, email)
- Interfaz web moderna y responsive
- Sistema de estados: Pendiente, Confirmado, Cancelado, Completado
- Protección de archivos sensibles con `.htaccess`
- Modo desarrollo/producción configurable
- Manejo seguro de errores

### Seguridad
- Protección contra path traversal
- Headers de seguridad configurados
- Errores no exponen información sensible en producción
- Debug mode solo disponible desde localhost

### Características Técnicas
- Autoloading de clases
- Patrón Singleton para conexión a BD
- Prepared statements (prevención SQL injection)
- Validación de datos en controladores
- Sistema de versionado de assets para evitar caché en desarrollo

### Documentación
- README completo con instrucciones de instalación
- Guía de desarrollo (DESARROLLO.md)
- Guía de seguridad (SEGURIDAD.md)
- Ejemplo de configuración (config.php.example)


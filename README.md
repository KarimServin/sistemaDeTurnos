# Sistema de Turnos en PHP - Arquitectura MVC

Sistema básico de gestión de turnos desarrollado en PHP siguiendo el patrón MVC (Modelo-Vista-Controlador) y buenas prácticas de desarrollo.

## Características

- ✅ Arquitectura MVC completa
- ✅ Separación de responsabilidades
- ✅ Código completamente en español
- ✅ Crear, editar, eliminar y listar turnos
- ✅ Filtros avanzados (fecha, estado, email)
- ✅ Validación de horarios disponibles
- ✅ Estados de turnos: Pendiente, Confirmado, Cancelado, Completado
- ✅ Interfaz web moderna y responsive
- ✅ API RESTful

## Estructura del Proyecto

```
sistemaDeTurnos/
├── app/
│   ├── config/
│   │   ├── config.php.example  # Ejemplo de configuración
│   │   └── config.php          # Configuración de la aplicación (crear desde .example)
│   ├── core/
│   │   ├── Autoloader.php      # Carga automática de clases
│   │   ├── BaseDeDatos.php     # Conexión a base de datos (Singleton)
│   │   ├── ControladorBase.php # Clase base para controladores
│   │   ├── Router.php          # Sistema de enrutamiento
│   │   └── ViewHelper.php      # Helper para vistas
│   ├── models/
│   │   └── Turno.php           # Modelo de turno
│   ├── controllers/
│   │   └── TurnoController.php # Controlador de turnos
│   ├── repositories/
│   │   ├── RepositorioBase.php # Interface para repositorios
│   │   └── TurnoRepository.php # Repositorio de turnos
│   └── views/
│       ├── layouts/
│       │   └── base.php        # Layout principal
│       ├── partials/           # Componentes reutilizables
│       └── turnos/
│           └── index.php       # Vista principal
├── public/
│   ├── index.php               # Punto de entrada
│   ├── .htaccess               # Configuración Apache
│   ├── css/
│   │   └── app.css            # Estilos principales
│   ├── js/
│   │   └── app.js             # JavaScript principal
│   └── verificar-assets.php   # Herramienta de desarrollo (opcional)
├── database.sql                # Esquema de la base de datos
├── package.json                # Configuración npm
├── README.md                    # Este archivo
├── DESARROLLO.md               # Guía de desarrollo
└── SEGURIDAD.md                # Guía de seguridad
```

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache con mod_rewrite) o PHP built-in server

## Instalación

1. **Importar la base de datos**

   Importa el archivo `database.sql` en tu servidor MySQL:
   ```bash
   mysql -u root -p < database.sql
   ```

   O ejecuta el contenido de `database.sql` en tu cliente MySQL.

2. **Configurar la aplicación**

   Copia el archivo de ejemplo y configura tus credenciales:
   ```bash
   cp app/config/config.php.example app/config/config.php
   ```
   
   Luego edita `app/config/config.php` con tus credenciales:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_turnos');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```
   
   ⚠️ **IMPORTANTE**: El archivo `config.php` está en `.gitignore` y no se subirá al repositorio. Nunca subas credenciales reales.

3. **Configurar el servidor web**

   **Opción A: Apache con mod_rewrite**
   
   Asegúrate de que el DocumentRoot apunte a la carpeta `public`:
   ```apache
   DocumentRoot /ruta/a/sistemaDeTurnos/public
   ```

   **Opción B: PHP built-in server**
   
   ```bash
   cd public
   php -S localhost:8000
   ```
   
   Luego accede a: `http://localhost:8000`

## Uso de la API

### Obtener todos los turnos
```
GET /api/turnos
GET /api/turnos?fecha=2024-01-15
GET /api/turnos?estado=confirmado&email=usuario@example.com
```

### Obtener un turno por ID
```
GET /api/turnos?id=1
```

### Crear un turno
```
POST /api/turnos
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "123456789",
  "fecha": "2024-01-15",
  "hora": "10:00",
  "servicio": "Consulta médica",
  "estado": "pendiente",
  "notas": "Primera consulta"
}
```

### Actualizar un turno
```
POST /api/turnos
Content-Type: application/json

{
  "id": 1,
  "estado": "confirmado"
}
```

### Eliminar un turno
```
POST /api/turnos
Content-Type: application/json

{
  "accion": "eliminar",
  "id": 1
}
```

O usando el método DELETE estándar:
```
DELETE /api/turnos/{id}
```

## Arquitectura MVC

### Modelo (Model)
- `Turno.php`: Maneja todas las operaciones con la base de datos relacionadas con turnos.

### Vista (View)
- `app/views/turnos/index.php`: Interfaz HTML para gestionar turnos.

### Controlador (Controller)
- `TurnoController.php`: Procesa las peticiones del usuario y coordina entre el modelo y la vista.

## Buenas Prácticas Aplicadas

1. **Separación de responsabilidades**: Cada clase tiene una responsabilidad única
2. **Patrón Singleton**: Para la conexión a base de datos
3. **Autoloading**: Carga automática de clases
4. **Enrutamiento**: Sistema de rutas limpio y mantenible
5. **Validación**: Validación de datos en el controlador
6. **Prepared statements**: Prevención de SQL injection
7. **Respuestas JSON**: API RESTful con respuestas estandarizadas
8. **Código en español**: Todas las variables y métodos en español

## Funcionalidades Adicionales

- **Validación de horarios**: El sistema verifica que no haya conflictos de horarios antes de crear un turno.
- **Estados de turnos**: Los turnos pueden tener 4 estados diferentes para un mejor control.
- **Filtros avanzados**: Permite buscar turnos por múltiples criterios.
- **Interfaz responsive**: Funciona bien en dispositivos móviles y tablets.

## Notas

- El sistema valida que no se puedan crear dos turnos en el mismo horario.
- Los turnos cancelados no ocupan el horario.
- La fecha mínima para crear turnos es la fecha actual.
- Todas las variables y métodos están en español.

## Documentación Adicional

- **[DESARROLLO.md](DESARROLLO.md)**: Guía de desarrollo y actualización automática de assets
- **[SEGURIDAD.md](SEGURIDAD.md)**: Guía de seguridad y mejores prácticas
- **[CHANGELOG.md](CHANGELOG.md)**: Historial de cambios

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Autor

Sistema de Turnos - Sistema de gestión de turnos desarrollado con PHP MVC# sistemaDeTurnos

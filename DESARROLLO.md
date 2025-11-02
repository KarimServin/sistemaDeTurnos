# Guía de Desarrollo - Actualización Automática de Assets

## 🚀 Actualización Automática Sin Guardar Manualmente

Para que los cambios en CSS/JS se reflejen automáticamente sin necesidad de guardar manualmente, sigue estos pasos:

### Opción 1: Auto-Save + Browser-Sync (Recomendado)

1. **Habilita Auto-Save en el Editor** (ya configurado en `.vscode/settings.json`):
   - El archivo se guarda automáticamente después de 500ms de inactividad
   - No necesitas presionar Ctrl+S

2. **Instala Browser-Sync** (si no lo tienes):
   ```bash
   npm install
   ```

3. **Ejecuta Browser-Sync**:
   ```bash
   npm run dev
   ```

   O si prefieres:
   ```bash
   npm run watch
   ```

4. **Abre la URL que muestra Browser-Sync** (normalmente `http://localhost:3000`)

5. **¡Listo!** Ahora:
   - Escribe en el CSS
   - Espera 500ms (auto-save)
   - El navegador recargará automáticamente con los cambios

### Opción 2: Solo Auto-Save (Sin Browser-Sync)

Si prefieres no usar Browser-Sync:

1. **Asegúrate de que auto-save esté activo** en tu editor
2. **Recarga manualmente** la página con Ctrl+F5 después de que se guarde
3. Los cambios se verán porque el sistema ya está configurado para no usar caché en desarrollo

### Configuración Actual

- ✅ **Auto-save**: Activado (500ms de delay)
- ✅ **Modo desarrollo**: Activado (`MODO_DESARROLLO = true`)
- ✅ **Sin caché**: Los assets siempre se recargan con versiones únicas
- ✅ **Browser-sync**: Configurado en `package.json`

### Cambiar a Modo Producción

Cuando termines el desarrollo, cambia en `app/config/config.php`:

```php
define('MODO_DESARROLLO', false);
```

Esto mejorará el rendimiento usando caché eficiente.

## 🔍 Verificar que Funciona

1. Abre `public/css/app.css`
2. Cambia un color (ej: `body { background: red; }`)
3. Espera medio segundo (auto-save)
4. Si usas Browser-Sync, verás la recarga automática
5. Si no, recarga la página (Ctrl+F5)

## 💡 Tips

- **Auto-save delay**: Puedes ajustarlo en `.vscode/settings.json` (actualmente 500ms)
- **Browser-sync**: También sincroniza scroll y clicks entre dispositivos
- **Desactivar auto-save**: Cambia `"files.autoSave": "off"` en settings.json


# Protección XSS - Sistema de Turnos

## ✅ Protecciones Implementadas contra XSS (Cross-Site Scripting)

### 1. **ViewHelper - Escape de Datos**

Todos los métodos de `ViewHelper` ahora escapan datos de usuario:

- ✅ `alerta()` - Escapa mensaje y tipo
- ✅ `boton()` - Escapa texto, tipo, atributos y valida onclick
- ✅ `campoFormulario()` - Escapa nombre, etiqueta, valor y opciones
- ✅ `badgeEstado()` - Valida estado permitido y escapa texto
- ✅ `e()` - Método auxiliar para escapar HTML

### 2. **Vistas PHP - Escape de Variables**

- ✅ `base.php` - Escapa `$titulo`, URLs de CSS/JS, y valida URLs adicionales
- ✅ `index.php` - Escapa `APP_NAME`
- ✅ URLs adicionales validadas con regex antes de incluirlas

### 3. **JavaScript - Escape de Datos del Usuario**

Creado `EscapadorHtml` para escapar datos antes de insertarlos en el DOM:

- ✅ `generarFilaTabla()` - Escapa todos los campos del turno (nombre, email, teléfono, etc.)
- ✅ `generarBadgeEstado()` - Valida estado y escapa texto
- ✅ `generarBotonesAcciones()` - Valida ID numérico antes de usarlo
- ✅ `editarTurno()` - Valida y escapa ID en URL
- ✅ `construirUrlConFiltros()` - Escapa parámetros con `encodeURIComponent()`
- ✅ `mostrarMensaje()` - Valida tipo y usa `textContent` (escapa automáticamente)
- ✅ `hacerPeticion()` - Valida método HTTP y maneja errores

### 4. **Validaciones de Seguridad**

- ✅ IDs validados como numéricos antes de usar
- ✅ Estados validados contra lista permitida
- ✅ Métodos HTTP validados contra lista permitida
- ✅ URLs validadas con regex antes de incluir en HTML
- ✅ Atributos onclick validados (solo funciones JavaScript seguras)

## 🛡️ Ejemplos de Ataques Prevenidos

### Antes (Vulnerable):
```javascript
// PELIGROSO - Permite inyección de código
<td>${turno.nombre}</td>  // Si nombre = "<script>alert('XSS')</script>"
```

### Después (Seguro):
```javascript
// SEGURO - Escapa el contenido
<td>${EscapadorHtml.escapar(turno.nombre)}</td>
// Resultado: <td>&lt;script&gt;alert('XSS')&lt;/script&gt;</td>
```

## 📋 Checklist de Seguridad XSS

- [x] Todos los datos de usuario escapados en PHP (ViewHelper)
- [x] Todos los datos de usuario escapados en JavaScript
- [x] IDs validados como numéricos
- [x] Estados validados contra lista permitida
- [x] URLs validadas antes de incluir
- [x] Atributos HTML escapados
- [x] Parámetros de URL codificados
- [x] Métodos HTTP validados

## ⚠️ Nota sobre $contenido

El `$contenido` en `base.php` NO se escapa porque:
- Viene de las vistas que usan `ViewHelper` (que ya escapa)
- Puede contener HTML válido generado por componentes
- Las vistas son responsables de escapar datos de usuario antes de incluirlos

**Buenas prácticas:**
- Siempre usar `ViewHelper::campoFormulario()` en lugar de `<input>` directo
- Siempre usar `ViewHelper::e($dato)` para escapar datos de usuario
- Nunca usar `echo $datoUsuario` directamente sin escapar

## 🔍 Cómo Verificar

1. Intenta crear un turno con nombre: `<script>alert('XSS')</script>`
2. Verifica que se muestre como texto, no como código ejecutado
3. Revisa el HTML generado - deberías ver `&lt;script&gt;` en lugar de `<script>`


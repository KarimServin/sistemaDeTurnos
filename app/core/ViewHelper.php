<?php
/**
 * Helper para vistas - Funciones auxiliares para renderizar elementos comunes
 */

class ViewHelper {
    
    /**
     * Generar un mensaje de alerta
     */
    public static function alerta($mensaje, $tipo = 'info') {
        $clase = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
        $mensajeEscapado = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        return "<div class='alerta alerta-{$clase}'>{$mensajeEscapado}</div>";
    }

    /**
     * Generar un botón
     */
    public static function boton($texto, $tipo = 'primario', $onclick = '', $atributos = '') {
        // Escapar todos los valores para prevenir XSS
        $textoEscapado = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        $tipoEscapado = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
        
        // Validar que onclick sea seguro (solo funciones JavaScript válidas, sin <script>)
        $onclickAttr = '';
        if ($onclick) {
            // Permitir solo llamadas a funciones seguras (validación básica)
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\([^)]*\)$/', $onclick)) {
                $onclickEscapado = htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8');
                $onclickAttr = "onclick='{$onclickEscapado}'";
            }
        }
        
        // Escapar atributos adicionales
        $atributosEscapados = htmlspecialchars($atributos, ENT_QUOTES, 'UTF-8');
        $clasesFinales = "boton boton-{$tipoEscapado}";
        $atributosFinales = trim("class='{$clasesFinales}' {$onclickAttr} {$atributosEscapados}");
        
        return "<button {$atributosFinales}>{$textoEscapado}</button>";
    }

    /**
     * Generar un campo de formulario
     */
    public static function campoFormulario($tipo, $nombre, $etiqueta, $valor = '', $requerido = false, $opciones = []) {
        // Escapar todos los valores para prevenir XSS
        $nombreEscapado = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $etiquetaEscapada = htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8');
        $tipoEscapado = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
        $valorEscapado = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        
        $html = "<div class='grupo-formulario'>";
        $html .= "<label for='{$nombreEscapado}'>{$etiquetaEscapada}" . ($requerido ? ' *' : '') . "</label>";
        
        switch ($tipo) {
            case 'text':
            case 'email':
            case 'tel':
            case 'date':
            case 'time':
                $requeridoAttr = $requerido ? 'required' : '';
                $html .= "<input type='{$tipoEscapado}' id='{$nombreEscapado}' name='{$nombreEscapado}' value='{$valorEscapado}' {$requeridoAttr}>";
                break;
                
            case 'textarea':
                $requeridoAttr = $requerido ? 'required' : '';
                $html .= "<textarea id='{$nombreEscapado}' name='{$nombreEscapado}' {$requeridoAttr}>{$valorEscapado}</textarea>";
                break;
                
            case 'select':
                $html .= "<select id='{$nombreEscapado}' name='{$nombreEscapado}'>";
                if (isset($opciones['opciones']) && is_array($opciones['opciones'])) {
                    foreach ($opciones['opciones'] as $valorOpcion => $textoOpcion) {
                        // Escapar valores y textos de opciones
                        $valorOpcionEscapado = htmlspecialchars($valorOpcion, ENT_QUOTES, 'UTF-8');
                        $textoOpcionEscapado = htmlspecialchars($textoOpcion, ENT_QUOTES, 'UTF-8');
                        $selected = ($valor == $valorOpcion) ? 'selected' : '';
                        $html .= "<option value='{$valorOpcionEscapado}' {$selected}>{$textoOpcionEscapado}</option>";
                    }
                }
                $html .= "</select>";
                break;
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Generar badge de estado
     */
    public static function badgeEstado($estado) {
        $textos = [
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'completado' => 'Completado'
        ];
        
        // Validar que el estado sea uno de los permitidos para evitar XSS en clase CSS
        $estadosPermitidos = ['pendiente', 'confirmado', 'cancelado', 'completado'];
        $estadoLimpio = in_array($estado, $estadosPermitidos) ? $estado : 'pendiente';
        
        $texto = $textos[$estadoLimpio] ?? htmlspecialchars($estadoLimpio, ENT_QUOTES, 'UTF-8');
        return "<span class='estado estado-{$estadoLimpio}'>" . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . "</span>";
    }

    /**
     * Generar URL de asset con versión para evitar caché
     */
    public static function asset($ruta) {
        // Detectar la ruta base automáticamente desde SCRIPT_NAME
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $rutaBase = rtrim($scriptName, '/');
        
        // Ruta completa del archivo
        $rutaArchivo = PUBLIC_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($ruta, '/'));
        
        // Agregar versión basada en la fecha de modificación del archivo + tamaño
        $version = '';
        if (file_exists($rutaArchivo)) {
            $ultimaModificacion = filemtime($rutaArchivo);
            $tamanoArchivo = filesize($rutaArchivo);
            
            // En modo desarrollo: usar timestamp siempre cambiante + microtime para forzar recarga constante
            if (defined('MODO_DESARROLLO') && MODO_DESARROLLO) {
                // Usar microtime para obtener un valor único en cada request
                // Esto hace que cada carga de página tenga una URL diferente
                $version = '?v=' . md5($ultimaModificacion . $tamanoArchivo) . '&t=' . time() . '&m=' . (int)(microtime(true) * 1000);
            } else {
                // En producción: usar solo hash del contenido (mejor rendimiento)
                $version = '?v=' . md5($ultimaModificacion . $tamanoArchivo);
            }
        }
        
        return $rutaBase . '/' . ltrim($ruta, '/') . $version;
    }

    /**
     * Escapar HTML
     */
    public static function e($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}
?>

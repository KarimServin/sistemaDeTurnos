<?php
/**
 * Helper para vistas - Funciones auxiliares para renderizar elementos comunes
 */

class ViewHelper {
    
    /**
     * Generar un mensaje de alerta
     */
    public static function alerta($mensaje, $tipo = 'info') {
        $clase = 'alerta alerta-' . $tipo;
        return "<div class='{$clase}'>{$mensaje}</div>";
    }

    /**
     * Generar un botón
     */
    public static function boton($texto, $tipo = 'primario', $onclick = '', $atributos = '') {
        $onclickAttr = $onclick ? "onclick='{$onclick}'" : '';
        $clasesFinales = "boton boton-{$tipo}";
        $atributosFinales = trim("class='{$clasesFinales}' {$onclickAttr} {$atributos}");
        return "<button {$atributosFinales}>{$texto}</button>";
    }

    /**
     * Generar un campo de formulario
     */
    public static function campoFormulario($tipo, $nombre, $etiqueta, $valor = '', $requerido = false, $opciones = []) {
        $html = "<div class='grupo-formulario'>";
        $html .= "<label for='{$nombre}'>{$etiqueta}" . ($requerido ? ' *' : '') . "</label>";
        
        switch ($tipo) {
            case 'text':
            case 'email':
            case 'tel':
            case 'date':
            case 'time':
                $requeridoAttr = $requerido ? 'required' : '';
                $html .= "<input type='{$tipo}' id='{$nombre}' name='{$nombre}' value='{$valor}' {$requeridoAttr}>";
                break;
                
            case 'textarea':
                $requeridoAttr = $requerido ? 'required' : '';
                $html .= "<textarea id='{$nombre}' name='{$nombre}' {$requeridoAttr}>{$valor}</textarea>";
                break;
                
            case 'select':
                $html .= "<select id='{$nombre}' name='{$nombre}'>";
                if (isset($opciones['opciones']) && is_array($opciones['opciones'])) {
                    foreach ($opciones['opciones'] as $valorOpcion => $textoOpcion) {
                        $selected = ($valor == $valorOpcion) ? 'selected' : '';
                        $html .= "<option value='{$valorOpcion}' {$selected}>{$textoOpcion}</option>";
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
        
        $texto = $textos[$estado] ?? $estado;
        return "<span class='estado estado-{$estado}'>{$texto}</span>";
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

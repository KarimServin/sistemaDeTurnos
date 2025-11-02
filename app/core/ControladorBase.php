<?php

/**
 * Clase base Controller - Todos los controladores deben extender de esta clase
 */

abstract class ControladorBase
{

    /**
     * Cargar una vista
     */
    protected function cargarVista($nombreVista, $datos = [])
    {
        // Hacer disponibles las variables en el scope de la vista
        extract($datos);

        // Incluir ViewHelper si no está ya cargado
        if (!class_exists('ViewHelper')) {
            require_once APP_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'ViewHelper.php';
        }

        $rutaVista = VIEWS_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nombreVista) . '.php';

        if (file_exists($rutaVista)) {
            require_once $rutaVista;
        } else {
            die("Vista no encontrada: $nombreVista");
        }
    }

    /**
     * Incluir un partial
     */
    protected function incluirPartial($nombrePartial, $datos = [])
    {
        extract($datos);
        $rutaPartial = VIEWS_PATH . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nombrePartial) . '.php';

        if (file_exists($rutaPartial)) {
            require_once $rutaPartial;
        }
    }

    /**
     * Enviar respuesta JSON
     */
    protected function respuestaJson($datos, $codigoHttp = 200)
    {
        // Limpiar cualquier salida previa
        if (ob_get_length()) {
            ob_clean();
        }

        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');

        $json = json_encode($datos, JSON_UNESCAPED_UNICODE);

        // Verificar si hubo error en la codificación JSON
        if ($json === false) {
            $json = json_encode([
                'exito' => false,
                'mensaje' => 'Error al codificar respuesta JSON',
                'error' => json_last_error_msg()
            ], JSON_UNESCAPED_UNICODE);
        }

        echo $json;
        exit;
    }

    /**
     * Obtener datos del cuerpo de la petición
     */
    protected function obtenerDatosPeticion()
    {
        // Primero intentar leer desde la variable global (si ya fue leída)
        if (isset($GLOBALS['_REQUEST_BODY'])) {
            $datos = json_decode($GLOBALS['_REQUEST_BODY'], true);
            if ($datos !== null) {
                return $datos;
            }
        }

        // Si no está en la global, intentar leer desde php://input
        $contenido = file_get_contents('php://input');
        if (!empty($contenido)) {
            $datos = json_decode($contenido, true);
            if ($datos !== null) {
                return $datos;
            }
        }

        // Por último, usar $_POST como fallback
        return $_POST;
    }

    /**
     * Validar campos requeridos
     */
    protected function validarCampos($datos, $camposRequeridos)
    {
        $faltantes = [];

        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                $faltantes[] = $campo;
            }
        }

        return $faltantes;
    }
}

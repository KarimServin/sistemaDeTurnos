<?php
/**
 * Clase Router - Maneja el enrutamiento de la aplicación
 */

class Enrutador {
    private $rutas = [];

    /**
     * Registrar una ruta GET
     */
    public function get($ruta, $controlador, $metodo) {
        $this->registrarRuta('GET', $ruta, $controlador, $metodo);
    }

    /**
     * Registrar una ruta POST
     */
    public function post($ruta, $controlador, $metodo) {
        $this->registrarRuta('POST', $ruta, $controlador, $metodo);
    }

    /**
     * Registrar una ruta
     */
    private function registrarRuta($metodoHttp, $ruta, $controlador, $metodo) {
        $this->rutas[] = [
            'metodo' => $metodoHttp,
            'ruta' => $ruta,
            'controlador' => $controlador,
            'accion' => $metodo
        ];
    }

    /**
     * Despachar la petición
     */
    public function despachar() {
        $metodoHttp = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->rutas as $ruta) {
            if ($ruta['metodo'] === $metodoHttp && $this->coincideRuta($ruta['ruta'], $uri)) {
                $controlador = new $ruta['controlador']();
                $accion = $ruta['accion'];
                
                if (method_exists($controlador, $accion)) {
                    return $controlador->$accion();
                }
            }
        }

        // Si no se encuentra la ruta
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Ruta no encontrada']);
    }

    /**
     * Verificar si la ruta coincide
     */
    private function coincideRuta($patron, $uri) {
        // Convertir parámetros {id} a expresiones regulares
        $patron = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $patron);
        $patron = '#^' . $patron . '$#';
        
        return preg_match($patron, $uri);
    }

    /**
     * Obtener parámetros de la ruta
     */
    public function obtenerParametros($patron, $uri) {
        $parametros = [];
        $patronOriginal = $patron;
        $patron = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $patron);
        $patron = '#^' . $patron . '$#';
        
        if (preg_match($patron, $uri, $coincidencias)) {
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $patronOriginal, $nombres);
            if (!empty($nombres[1])) {
                for ($i = 0; $i < count($nombres[1]); $i++) {
                    $parametros[$nombres[1][$i]] = $coincidencias[$i + 1];
                }
            }
        }
        
        return $parametros;
    }

    /**
     * Obtener la ruta actual
     */
    public function obtenerRutaActual() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }
}
?>

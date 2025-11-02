<?php
/**
 * Clase Autoloader - Carga automática de clases
 */

class Autoloader {
    
    /**
     * Registrar el autoloader
     */
    public function registrar() {
        spl_autoload_register([$this, 'cargarClase']);
    }

    /**
     * Cargar una clase
     */
    private function cargarClase($nombreClase) {
        // Convertir nombre de clase a ruta de archivo
        $directorios = [
            CORE_PATH,
            MODELS_PATH,
            CONTROLLERS_PATH,
            REPOSITORIES_PATH
        ];

        foreach ($directorios as $directorio) {
            $rutaArchivo = $directorio . DIRECTORY_SEPARATOR . $nombreClase . '.php';
            
            if (file_exists($rutaArchivo)) {
                require_once $rutaArchivo;
                return;
            }
        }
    }
}
?>

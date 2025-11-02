<?php
/**
 * Clase Database - Patrón Singleton para conexión a base de datos
 */

class BaseDeDatos {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        // Intentar diferentes métodos de conexión
        $metodosConexion = [
            // Método 1: localhost (por defecto)
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            // Método 2: 127.0.0.1 (a veces funciona mejor)
            "mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            // Método 3: localhost con puerto explícito
            "mysql:host=localhost;port=3306;dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            // Método 4: Sin especificar dbname primero, luego la seleccionamos
            "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        ];
        
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $ultimoError = null;
        
        foreach ($metodosConexion as $dsn) {
            try {
                $this->conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
                
                // Si no se especificó dbname, seleccionarla ahora
                if (strpos($dsn, 'dbname') === false) {
                    $this->conexion->exec("USE " . DB_NAME);
                }
                
                // Si llegamos aquí, la conexión fue exitosa
                return;
            } catch (PDOException $e) {
                $ultimoError = $e;
                // Continuar con el siguiente método
                continue;
            }
        }
        
        // Si todos los métodos fallaron, lanzar el último error
        try {
            throw $ultimoError;
        } catch (PDOException $e) {
            // Registrar el error completo en el log (seguro)
            error_log('Error de conexión a BD: ' . $e->getMessage() . ' | Host: ' . DB_HOST . ' | DB: ' . DB_NAME . ' | User: ' . DB_USER);
            
            // Determinar modo para mostrar mensajes apropiados
            $esDesarrollo = defined('MODO_DESARROLLO') && MODO_DESARROLLO;
            
            if ($esDesarrollo) {
                // En desarrollo: mostrar información útil para debugging
                $mensaje = "Error de conexión a la base de datos: " . htmlspecialchars($e->getMessage());
                
                // Mensajes más claros para errores comunes
                if (strpos($e->getMessage(), 'Access denied') !== false) {
                    $mensaje .= "\n\n💡 SOLUCIÓN: ";
                    if (empty(DB_PASS)) {
                        $mensaje .= "Tu MySQL requiere una contraseña. Actualiza DB_PASS en app/config/config.php";
                    } else {
                        $mensaje .= "La contraseña es incorrecta. Verifica DB_PASS en app/config/config.php";
                    }
                } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                    $mensaje .= "\n\n💡 SOLUCIÓN: Importa el archivo database.sql en MySQL para crear la base de datos.";
                }
            } else {
                // En producción: mensaje genérico sin exponer información sensible
                http_response_code(500);
                $mensaje = "Error de conexión a la base de datos. Por favor, contacte al administrador del sistema.";
            }
            
            die($mensaje);
        }
    }

    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtenerConexion() {
        return $this->conexion;
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar el singleton");
    }
}
?>


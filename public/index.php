<?php

/**
 * Punto de entrada de la aplicación
 * Sistema de Turnos - Arquitectura MVC
 */

// Evitar cualquier salida previa que pueda interferir con JSON
if (ob_get_length()) {
    ob_clean();
}

// Cargar configuración primero para determinar modo
$configPath = dirname(__DIR__) . '/app/config/config.php';
if (!file_exists($configPath)) {
    // No exponer la ruta real en producción
    http_response_code(500);
    die('Error: Error de configuración del sistema');
}
require_once $configPath;

// Configurar manejo de errores según el modo
if (defined('MODO_DESARROLLO') && MODO_DESARROLLO) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Cargar autoloader
$autoloaderPath = APP_PATH . '/core/Autoloader.php';
if (!file_exists($autoloaderPath)) {
    http_response_code(500);
    if (defined('MODO_DESARROLLO') && MODO_DESARROLLO) {
        die('Error: No se encuentra Autoloader.php');
    } else {
        die('Error: Error interno del sistema');
    }
}
require_once $autoloaderPath;

// Registrar autoloader
$autoloader = new Autoloader();
$autoloader->registrar();

/**
 * Obtener la URI normalizada de la petición
 */
function obtenerUri()
{
    // Obtener la URI completa de la petición
    $uriCompleta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uriCompleta = rtrim($uriCompleta, '/');

    // Detectar la ruta base de la aplicación (ej: /sistemaDeTurnos/public)
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $rutaBase = rtrim($scriptName, '/');

    // Remover la ruta base de la URI para obtener la ruta relativa
    $uri = $uriCompleta;
    if ($rutaBase !== '/' && strpos($uri, $rutaBase) === 0) {
        $uri = substr($uri, strlen($rutaBase));
    }

    // Normalizar la URI
    $uri = rtrim($uri, '/');
    if (empty($uri)) {
        $uri = '/';
    }

    return $uri;
}

/**
 * Servir archivo estático si existe
 */
function servirArchivoEstatico($uri)
{
    // Remover query string de la URI si existe (para URLs como css/app.css?v=123&t=456)
    $uriSinQuery = parse_url($uri, PHP_URL_PATH);

    // Detectar y remover la ruta base si existe (ej: /sistemaDeTurnos/public/css/app.css)
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $rutaBase = rtrim($scriptName, '/');

    // Remover la ruta base de la URI
    if ($rutaBase !== '/' && $rutaBase !== '' && strpos($uriSinQuery, $rutaBase) === 0) {
        $uriSinQuery = substr($uriSinQuery, strlen($rutaBase));
    }

    // Normalizar: remover /public si existe en la URI
    $uriSinQuery = str_replace('/public', '', $uriSinQuery);

    // Patrones para archivos estáticos
    $patronesEstaticos = [
        '#^/(css|js|images?)/.+#',
        '#\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$#'
    ];

    $esArchivoEstatico = false;
    foreach ($patronesEstaticos as $patron) {
        if (preg_match($patron, $uriSinQuery)) {
            $esArchivoEstatico = true;
            break;
        }
    }

    if (!$esArchivoEstatico) {
        return false;
    }

    // Construir ruta del archivo (sin query string, sin ruta base)
    $rutaArchivo = PUBLIC_PATH . $uriSinQuery;

    // SEGURIDAD: Prevenir path traversal (../, ..\, etc.)
    $rutaReal = realpath($rutaArchivo);
    $rutaPublicReal = realpath(PUBLIC_PATH);

    // Verificar que el archivo está dentro de PUBLIC_PATH (previene path traversal)
    if (
        $rutaReal === false || $rutaPublicReal === false ||
        strpos($rutaReal, $rutaPublicReal) !== 0
    ) {
        return false;
    }

    // Verificar que el archivo existe y es un archivo regular
    if (!file_exists($rutaReal) || !is_file($rutaReal)) {
        return false;
    }

    // Usar la ruta real para servir el archivo (más seguro)
    $rutaArchivo = $rutaReal;

    // Determinar tipo MIME
    $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
    $tiposMime = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject'
    ];

    $tipoMime = $tiposMime[$extension] ?? 'application/octet-stream';

    // Headers para evitar caché en desarrollo (siempre para CSS y JS)
    if (in_array($extension, ['css', 'js'])) {
        // Headers agresivos para evitar caché completamente
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');

        // ETag basado en la fecha de modificación del archivo
        $ultimaModificacion = filemtime($rutaArchivo);
        $etag = md5($rutaArchivo . $ultimaModificacion . filesize($rutaArchivo));
        header('ETag: "' . $etag . '"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $ultimaModificacion) . ' GMT');

        // Si el cliente envía un If-None-Match o If-Modified-Since, forzar revalidación
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) || isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // En desarrollo, siempre servir el archivo nuevo
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }
    }

    header('Content-Type: ' . $tipoMime);
    header('Content-Length: ' . filesize($rutaArchivo));

    readfile($rutaArchivo);
    exit;
}

// Obtener URI y método HTTP
$uri = obtenerUri();
$metodo = $_SERVER['REQUEST_METHOD'];

// Servir archivos estáticos antes de procesar rutas
// Usar REQUEST_URI directamente (incluye query string si existe) para servir archivos estáticos
if (servirArchivoEstatico($_SERVER['REQUEST_URI'])) {
    // El archivo ya fue servido y se hizo exit
    return;
}

// Debug: solo en modo desarrollo y con validación
if (isset($_GET['debug'])) {
    // Solo permitir debug en modo desarrollo
    if (!defined('MODO_DESARROLLO') || !MODO_DESARROLLO) {
        http_response_code(403);
        die('Debug mode disabled');
    }

    // Validar que viene de localhost (seguridad adicional)
    $ipRemota = $_SERVER['REMOTE_ADDR'] ?? '';
    $esLocal = in_array($ipRemota, ['127.0.0.1', '::1', 'localhost']) ||
        strpos($ipRemota, '127.') === 0;

    if (!$esLocal) {
        http_response_code(403);
        die('Debug mode only available from localhost');
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo "REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "URI Procesada: " . htmlspecialchars($uri) . "\n";
    echo "Método: " . htmlspecialchars($metodo) . "\n";
    echo "PUBLIC_PATH: [REDACTED]\n"; // No exponer rutas reales
    exit;
}

// Crear instancia del controlador
$controlador = new TurnoController();

// Enrutamiento basado en URI y método HTTP
try {
    // Ruta principal - Vista de turnos
    if ($uri === '/' && $metodo === 'GET') {
        $controlador->index();
    }
    // API - Obtener todos los turnos
    elseif ($uri === '/api/turnos' && $metodo === 'GET') {
        $controlador->obtenerTodos();
    }
    // API - Obtener turno por ID
    elseif (preg_match('#^/api/turnos/(\d+)$#', $uri, $matches) && $metodo === 'GET') {
        $_GET['id'] = $matches[1];
        $controlador->obtenerPorId();
    }
    // API - Crear nuevo turno (POST a /api/turnos)
    elseif ($uri === '/api/turnos' && $metodo === 'POST') {
        // Leer el cuerpo de la petición una vez
        $contenido = file_get_contents('php://input');
        $datos = json_decode($contenido, true) ?: $_POST;
        $accion = $datos['accion'] ?? '';

        // Asegurar que los datos estén disponibles para el controlador
        // Guardar el contenido en una variable global o en $_POST para que el controlador lo pueda leer
        $_POST = array_merge($_POST, $datos);

        // También guardar el contenido JSON para que obtenerDatosPeticion() pueda leerlo
        $GLOBALS['_REQUEST_BODY'] = $contenido;

        // Soporte para parámetro 'accion' (compatibilidad con versiones anteriores)
        // IMPORTANTE: Verificar 'eliminar' ANTES de 'actualizar' porque ambos pueden tener 'id'
        if ($accion === 'eliminar') {
            $controlador->eliminar();
        } elseif ($accion === 'crear') {
            $controlador->crear();
        } elseif ($accion === 'actualizar' || isset($datos['id'])) {
            $controlador->actualizar();
        } else {
            // Por defecto, intentar crear
            $controlador->crear();
        }
    }
    // API - Actualizar turno (PUT/PATCH a /api/turnos/{id})
    elseif (preg_match('#^/api/turnos/(\d+)$#', $uri, $matches) && ($metodo === 'PUT' || $metodo === 'PATCH')) {
        $datos = json_decode(file_get_contents('php://input'), true) ?: [];
        $datos['id'] = $matches[1];
        // Simular PUT/PATCH a través de POST con accion=actualizar
        $_POST = $datos;
        $_POST['accion'] = 'actualizar';
        $controlador->actualizar();
    }
    // API - Eliminar turno (DELETE a /api/turnos/{id})
    elseif (preg_match('#^/api/turnos/(\d+)$#', $uri, $matches) && $metodo === 'DELETE') {
        $_POST = ['id' => $matches[1], 'accion' => 'eliminar'];
        $controlador->eliminar();
    }
    // Ruta no encontrada
    else {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Ruta no encontrada',
            'uri' => $uri,
            'metodo' => $metodo
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Manejo de errores seguro
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    // Registrar el error completo en el log
    error_log('Error en index.php: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

    // Solo mostrar detalles del error en desarrollo
    $mensajeError = 'Error interno del servidor';
    if (defined('MODO_DESARROLLO') && MODO_DESARROLLO && ini_get('display_errors')) {
        $mensajeError = $e->getMessage();
    }

    echo json_encode([
        'exito' => false,
        'mensaje' => $mensajeError
    ], JSON_UNESCAPED_UNICODE);
}

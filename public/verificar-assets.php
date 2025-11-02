<?php

/**
 * Script para verificar que los assets se carguen correctamente
 * 
 * NOTA: Este script es solo para desarrollo. En producción, deberías eliminarlo
 * o protegerlo para que solo sea accesible desde localhost.
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// Solo permitir acceso en desarrollo o desde localhost
if (!defined('MODO_DESARROLLO') || !MODO_DESARROLLO) {
    $ipRemota = $_SERVER['REMOTE_ADDR'] ?? '';
    $esLocal = in_array($ipRemota, ['127.0.0.1', '::1', 'localhost']) ||
        strpos($ipRemota, '127.') === 0;

    if (!$esLocal) {
        http_response_code(403);
        die('Este script solo está disponible en desarrollo o desde localhost');
    }
}

require_once APP_PATH . '/core/ViewHelper.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Verificación de Assets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .test {
            margin: 20px 0;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .ok {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        code {
            background: #e9ecef;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <h1>Verificación de Assets</h1>

    <?php
    $rutaCSS = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'app.css';
    $rutaJS = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js';

    // Verificar archivos
    echo '<div class="test ' . (file_exists($rutaCSS) ? 'ok' : 'error') . '">';
    echo '<strong>Archivo CSS:</strong> ';
    if (file_exists($rutaCSS)) {
        $fechaMod = filemtime($rutaCSS);
        echo '✅ Existe<br>';
        echo 'Última modificación: ' . date('Y-m-d H:i:s', $fechaMod) . '<br>';
    } else {
        echo '❌ No encontrado en: ' . $rutaCSS;
    }
    echo '</div>';

    echo '<div class="test ' . (file_exists($rutaJS) ? 'ok' : 'error') . '">';
    echo '<strong>Archivo JS:</strong> ';
    if (file_exists($rutaJS)) {
        $fechaMod = filemtime($rutaJS);
        echo '✅ Existe<br>';
        echo 'Última modificación: ' . date('Y-m-d H:i:s', $fechaMod) . '<br>';
    } else {
        echo '❌ No encontrado en: ' . $rutaJS;
    }
    echo '</div>';

    // Verificar URL generada
    echo '<div class="test ok">';
    echo '<strong>URL CSS generada:</strong><br>';
    $urlCSS = ViewHelper::asset('css/app.css');
    echo '<code>' . htmlspecialchars($urlCSS) . '</code><br><br>';

    echo '<strong>URL JS generada:</strong><br>';
    $urlJS = ViewHelper::asset('js/app.js');
    echo '<code>' . htmlspecialchars($urlJS) . '</code>';
    echo '</div>';

    // Verificar PUBLIC_PATH
    echo '<div class="test ok">';
    echo '<strong>PUBLIC_PATH:</strong> <code>' . PUBLIC_PATH . '</code><br>';
    echo '<strong>APP_PATH:</strong> <code>' . APP_PATH . '</code>';
    echo '</div>';

    // Instrucciones
    echo '<div class="test ok">';
    echo '<h3>📝 Instrucciones para probar:</h3>';
    echo '<ol>';
    echo '<li>Cambia algo en <code>public/css/app.css</code> (por ejemplo, el color del body)</li>';
    echo '<li>Guarda el archivo (Ctrl+S)</li>';
    echo '<li>Recarga esta página y verifica que cambie la "Última modificación"</li>';
    echo '<li>Ve a la página principal y recarga (F5 o Ctrl+R)</li>';
    echo '<li>Los cambios deberían aparecer inmediatamente</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <p><a href="/sistemaDeTurnos/public/">← Volver a la aplicación</a></p>
</body>

</html>
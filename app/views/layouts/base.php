<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo isset($titulo) ? htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') : htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php 
    $cssUrl = ViewHelper::asset('css/app.css');
    // Debug: solo mostrar en modo desarrollo
    if ((defined('MODO_DESARROLLO') && MODO_DESARROLLO && isset($_GET['debug']))) {
        echo "<!-- CSS URL: " . htmlspecialchars($cssUrl) . " -->\n    ";
    }
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8'); ?>" id="main-css">
    <?php if (isset($cssAdicional) && is_array($cssAdicional)): ?>
        <?php foreach ($cssAdicional as $css): ?>
            <?php 
            // Validar que sea una URL segura (relativa o http/https)
            if (preg_match('/^(https?:\/\/|\/|\.\/)[^<>"\']+$/i', $css)) {
                $cssEscapado = htmlspecialchars($css, ENT_QUOTES, 'UTF-8');
                echo "<link rel='stylesheet' href='{$cssEscapado}'>";
            }
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="contenedor">
        <?php if (isset($contenido)): ?>
            <?php 
            // $contenido ya viene escapado desde las vistas (usando htmlspecialchars en ViewHelper)
            // No escapamos aquí porque el contenido puede contener HTML válido generado por ViewHelper
            // Las vistas deben asegurarse de escapar cualquier dato de usuario antes de incluirlo
            echo $contenido; 
            ?>
        <?php endif; ?>
    </div>
    <script src="<?php echo htmlspecialchars(ViewHelper::asset('js/app.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php if (isset($jsAdicional) && is_array($jsAdicional)): ?>
        <?php foreach ($jsAdicional as $js): ?>
            <?php 
            // Validar que sea una URL segura (relativa o http/https)
            if (preg_match('/^(https?:\/\/|\/|\.\/)[^<>"\']+$/i', $js)) {
                $jsEscapado = htmlspecialchars($js, ENT_QUOTES, 'UTF-8');
                echo "<script src='{$jsEscapado}'></script>";
            }
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

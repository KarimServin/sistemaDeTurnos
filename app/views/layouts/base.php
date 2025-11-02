<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo isset($titulo) ? $titulo . ' - ' . APP_NAME : APP_NAME; ?></title>
    <?php 
    $cssUrl = ViewHelper::asset('css/app.css');
    // Debug: solo mostrar en modo desarrollo
    if ((defined('MODO_DESARROLLO') && MODO_DESARROLLO && isset($_GET['debug']))) {
        echo "<!-- CSS URL: " . htmlspecialchars($cssUrl) . " -->\n    ";
    }
    ?>
    <link rel="stylesheet" href="<?php echo $cssUrl; ?>" id="main-css">
    <?php if (isset($cssAdicional)): ?>
        <?php foreach ($cssAdicional as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="contenedor">
        <?php if (isset($contenido)): ?>
            <?php echo $contenido; ?>
        <?php endif; ?>
    </div>
    <script src="<?php echo ViewHelper::asset('js/app.js'); ?>"></script>
    <?php if (isset($jsAdicional)): ?>
        <?php foreach ($jsAdicional as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

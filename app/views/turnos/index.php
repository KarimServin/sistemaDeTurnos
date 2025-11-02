<?php
/**
 * Vista principal de turnos
 */

// Asegurar que ViewHelper esté cargado (los partials lo necesitan)
if (!class_exists('ViewHelper')) {
    require_once CORE_PATH . DIRECTORY_SEPARATOR . 'ViewHelper.php';
}

// Establecer título para el layout
$titulo = 'Gestión de Turnos';

// Iniciar buffer de salida para capturar el contenido
ob_start();
?>

<header class="cabecera">
    <h1><?php echo APP_NAME; ?></h1>
    <p>Sistema de gestión de turnos</p>
</header>

<main class="contenido-principal">
    <!-- Sección del formulario -->
    <section class="seccion-formulario">
        <?php 
        // Incluir el partial del formulario
        $rutaPartial = VIEWS_PATH . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'formulario-turno.php';
        if (file_exists($rutaPartial)) {
            require_once $rutaPartial;
        }
        ?>
    </section>

    <!-- Sección de filtros -->
    <section class="seccion-filtros">
        <?php 
        // Incluir el partial de filtros
        $rutaPartial = VIEWS_PATH . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'filtros-turnos.php';
        if (file_exists($rutaPartial)) {
            require_once $rutaPartial;
        }
        ?>
    </section>

    <!-- Sección de la tabla de turnos -->
    <section class="seccion-tabla">
        <?php 
        // Incluir el partial de la tabla
        $rutaPartial = VIEWS_PATH . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'tabla-turnos.php';
        if (file_exists($rutaPartial)) {
            require_once $rutaPartial;
        }
        ?>
    </section>
</main>

<?php
// Capturar el contenido en una variable
$contenido = ob_get_clean();

// Cargar el layout base con el contenido
$rutaLayout = VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'base.php';
if (file_exists($rutaLayout)) {
    require_once $rutaLayout;
}
?>


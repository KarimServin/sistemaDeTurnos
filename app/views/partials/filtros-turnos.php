<div>
    <h2>Filtros</h2>
    <div class="filtros">
        <input type="date" id="filtro-fecha" placeholder="Filtrar por fecha">
        <select id="filtro-estado">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="confirmado">Confirmado</option>
            <option value="cancelado">Cancelado</option>
            <option value="completado">Completado</option>
        </select>
        <input type="email" id="filtro-email" placeholder="Filtrar por email">
        <?php echo ViewHelper::boton('Buscar', 'primario', 'cargarTurnos()'); ?>
        <?php echo ViewHelper::boton('Limpiar', 'exito', 'limpiarFiltros()'); ?>
    </div>
</div>

<div class="contenedor-formulario">
    <h2 id="titulo-formulario">Nuevo Turno</h2>
    <form id="formulario-turno">
        <input type="hidden" id="turno-id" name="id">
        
        <div class="fila-formulario">
            <?php echo ViewHelper::campoFormulario('text', 'nombre', 'Nombre', '', true); ?>
            <?php echo ViewHelper::campoFormulario('email', 'email', 'Email', '', true); ?>
            <?php echo ViewHelper::campoFormulario('tel', 'telefono', 'Teléfono', '', true); ?>
        </div>

        <div class="fila-formulario">
            <?php echo ViewHelper::campoFormulario('date', 'fecha', 'Fecha', '', true); ?>
            <?php echo ViewHelper::campoFormulario('time', 'hora', 'Hora', '', true); ?>
            <?php echo ViewHelper::campoFormulario('text', 'servicio', 'Servicio', '', true); ?>
        </div>

        <div class="fila-formulario">
            <?php 
            $opcionesEstado = [
                'pendiente' => 'Pendiente',
                'confirmado' => 'Confirmado',
                'cancelado' => 'Cancelado',
                'completado' => 'Completado'
            ];
            echo ViewHelper::campoFormulario('select', 'estado', 'Estado', 'pendiente', false, ['opciones' => $opcionesEstado]);
            ?>
            <div class="grupo-formulario" style="grid-column: span 2;">
                <label for="notas">Notas</label>
                <textarea id="notas" name="notas"></textarea>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="boton boton-primario">Guardar Turno</button>
            <button type="button" class="boton boton-peligro" id="boton-cancelar" style="display: none;" onclick="cancelarEdicion()">Cancelar</button>
        </div>
    </form>
</div>

<?php
/**
 * Interfaz base para repositorios
 * Define el contrato mínimo que deben cumplir todos los repositorios
 */

interface RepositorioBase {
    /**
     * Guardar una entidad (crear o actualizar)
     */
    public function guardar($entidad);
    
    /**
     * Eliminar una entidad por su ID
     */
    public function eliminar($id);
    
    /**
     * Obtener una entidad por su ID
     */
    public function obtenerPorId($id);
    
    /**
     * Obtener todas las entidades
     */
    public function obtenerTodos($filtros = []);
}
?>

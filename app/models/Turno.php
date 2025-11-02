<?php
/**
 * Modelo Turno - Entidad que representa un turno
 * Solo contiene propiedades y métodos de acceso (getters/setters)
 * Sin lógica de persistencia
 */

class Turno {
    private $id;
    private $nombre;
    private $email;
    private $telefono;
    private $fecha;
    private $hora;
    private $servicio;
    private $estado;
    private $notas;
    private $creadoEn;
    private $actualizadoEn;

    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->cargarDesdeArray($datos);
        } else {
            $this->estado = 'pendiente';
        }
    }

    /**
     * Cargar datos desde un array
     */
    public function cargarDesdeArray($datos) {
        if (isset($datos['id'])) $this->setId($datos['id']);
        if (isset($datos['nombre'])) $this->setNombre($datos['nombre']);
        if (isset($datos['email'])) $this->setEmail($datos['email']);
        if (isset($datos['telefono'])) $this->setTelefono($datos['telefono']);
        if (isset($datos['fecha'])) $this->setFecha($datos['fecha']);
        if (isset($datos['hora'])) $this->setHora($datos['hora']);
        if (isset($datos['servicio'])) $this->setServicio($datos['servicio']);
        if (isset($datos['estado'])) $this->setEstado($datos['estado']);
        if (isset($datos['notas'])) $this->setNotas($datos['notas']);
        if (isset($datos['creado_en'])) $this->setCreadoEn($datos['creado_en']);
        if (isset($datos['actualizado_en'])) $this->setActualizadoEn($datos['actualizado_en']);
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getServicio() {
        return $this->servicio;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getNotas() {
        return $this->notas;
    }

    public function getCreadoEn() {
        return $this->creadoEn;
    }

    public function getActualizadoEn() {
        return $this->actualizadoEn;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }

    public function setServicio($servicio) {
        $this->servicio = $servicio;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setNotas($notas) {
        $this->notas = $notas;
    }

    public function setCreadoEn($creadoEn) {
        $this->creadoEn = $creadoEn;
    }

    public function setActualizadoEn($actualizadoEn) {
        $this->actualizadoEn = $actualizadoEn;
    }

    /**
     * Convertir el objeto a array
     */
    public function toArray() {
        return [
            'id' => $this->id ?? null,
            'nombre' => $this->nombre ?? '',
            'email' => $this->email ?? '',
            'telefono' => $this->telefono ?? '',
            'fecha' => $this->fecha ?? '',
            'hora' => $this->hora ?? '',
            'servicio' => $this->servicio ?? '',
            'estado' => $this->estado ?? 'pendiente',
            'notas' => $this->notas ?? '',
            'creado_en' => $this->creadoEn ?? null,
            'actualizado_en' => $this->actualizadoEn ?? null
        ];
    }
}
?>
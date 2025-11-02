<?php
/**
 * Repositorio Turno - Maneja toda la persistencia de turnos en la base de datos
 */

class TurnoRepository implements RepositorioBase {
    private $db;
    private $tabla = 'turnos';

    public function __construct() {
        $this->db = BaseDeDatos::obtenerInstancia()->obtenerConexion();
    }

    /**
     * Crear un nuevo turno en la base de datos
     */
    public function crear($turno) {
        $sql = "INSERT INTO {$this->tabla} (nombre, email, telefono, fecha, hora, servicio, estado, notas) 
                VALUES (:nombre, :email, :telefono, :fecha, :hora, :servicio, :estado, :notas)";
        
        $sentencia = $this->db->prepare($sql);
        $sentencia->execute([
            ':nombre' => $turno->getNombre(),
            ':email' => $turno->getEmail(),
            ':telefono' => $turno->getTelefono(),
            ':fecha' => $turno->getFecha(),
            ':hora' => $turno->getHora(),
            ':servicio' => $turno->getServicio(),
            ':estado' => $turno->getEstado(),
            ':notas' => $turno->getNotas()
        ]);

        $id = $this->db->lastInsertId();
        $turno->setId($id);
        
        return $id;
    }

    /**
     * Guardar un turno (crear o actualizar)
     */
    public function guardar($turno) {
        if ($turno->getId()) {
            return $this->actualizar($turno);
        } else {
            return $this->crear($turno);
        }
    }

    /**
     * Actualizar un turno existente
     */
    public function actualizar($turno) {
        $sql = "UPDATE {$this->tabla} SET 
                nombre = :nombre,
                email = :email,
                telefono = :telefono,
                fecha = :fecha,
                hora = :hora,
                servicio = :servicio,
                estado = :estado,
                notas = :notas
                WHERE id = :id";
        
        $sentencia = $this->db->prepare($sql);
        
        return $sentencia->execute([
            ':id' => $turno->getId(),
            ':nombre' => $turno->getNombre(),
            ':email' => $turno->getEmail(),
            ':telefono' => $turno->getTelefono(),
            ':fecha' => $turno->getFecha(),
            ':hora' => $turno->getHora(),
            ':servicio' => $turno->getServicio(),
            ':estado' => $turno->getEstado(),
            ':notas' => $turno->getNotas()
        ]);
    }

    /**
     * Obtener todos los turnos con filtros opcionales
     */
    public function obtenerTodos($filtros = []) {
        $sql = "SELECT * FROM {$this->tabla} WHERE 1=1";
        $parametros = [];

        if (!empty($filtros['fecha'])) {
            $sql .= " AND fecha = :fecha";
            $parametros[':fecha'] = $filtros['fecha'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $parametros[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['email'])) {
            $sql .= " AND email = :email";
            $parametros[':email'] = $filtros['email'];
        }

        $sql .= " ORDER BY fecha ASC, hora ASC";

        $sentencia = $this->db->prepare($sql);
        $sentencia->execute($parametros);

        $resultados = $sentencia->fetchAll();
        
        // Convertir arrays a objetos Turno
        $turnos = [];
        foreach ($resultados as $fila) {
            $turnos[] = $this->mapearAFila($fila);
        }
        
        return $turnos;
    }

    /**
     * Obtener un turno por su ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM {$this->tabla} WHERE id = :id";
        $sentencia = $this->db->prepare($sql);
        $sentencia->execute([':id' => $id]);

        $fila = $sentencia->fetch();
        
        if ($fila) {
            return $this->mapearAFila($fila);
        }
        
        return null;
    }

    /**
     * Eliminar un turno por su ID
     */
    public function eliminar($id) {
        // Validar que el ID sea numérico
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->tabla} WHERE id = :id";
        $sentencia = $this->db->prepare($sql);
        
        $exito = $sentencia->execute([':id' => $id]);
        
        // Verificar si realmente se eliminó alguna fila
        if ($exito && $sentencia->rowCount() > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Verificar si un horario está disponible
     */
    public function horarioDisponible($fecha, $hora, $excluirId = null) {
        $sql = "SELECT COUNT(*) as cantidad FROM {$this->tabla} 
                WHERE fecha = :fecha AND hora = :hora AND estado != 'cancelado'";
        
        $parametros = [
            ':fecha' => $fecha,
            ':hora' => $hora
        ];
        
        if ($excluirId !== null) {
            $sql .= " AND id != :excluir_id";
            $parametros[':excluir_id'] = $excluirId;
        }
        
        $sentencia = $this->db->prepare($sql);
        $sentencia->execute($parametros);

        $resultado = $sentencia->fetch();
        return $resultado['cantidad'] == 0;
    }

    /**
     * Obtener turnos por fecha
     */
    public function obtenerPorFecha($fecha) {
        $sql = "SELECT * FROM {$this->tabla} WHERE fecha = :fecha ORDER BY hora ASC";
        $sentencia = $this->db->prepare($sql);
        $sentencia->execute([':fecha' => $fecha]);

        $resultados = $sentencia->fetchAll();
        
        // Convertir arrays a objetos Turno
        $turnos = [];
        foreach ($resultados as $fila) {
            $turnos[] = $this->mapearAFila($fila);
        }
        
        return $turnos;
    }

    /**
     * Mapear una fila de la base de datos a un objeto Turno
     */
    private function mapearAFila($fila) {
        $turno = new Turno();
        $turno->setId($fila['id'] ?? null);
        $turno->setNombre($fila['nombre'] ?? '');
        $turno->setEmail($fila['email'] ?? '');
        $turno->setTelefono($fila['telefono'] ?? '');
        
        // Asegurar que fecha y hora estén en el formato correcto
        $fecha = $fila['fecha'] ?? null;
        if ($fecha) {
            // Si viene como DateTime o string, convertir a formato YYYY-MM-DD
            if ($fecha instanceof DateTime) {
                $fecha = $fecha->format('Y-m-d');
            } elseif (is_string($fecha)) {
                // Si viene con hora, extraer solo la fecha
                $fecha = explode(' ', $fecha)[0];
            }
        }
        $turno->setFecha($fecha ?? '');
        
        $hora = $fila['hora'] ?? null;
        if ($hora) {
            // Si viene como DateTime o string, convertir a formato HH:mm
            if ($hora instanceof DateTime) {
                $hora = $hora->format('H:i');
            } elseif (is_string($hora)) {
                // Si viene con segundos, extraer solo HH:mm
                $hora = substr($hora, 0, 5);
            }
        }
        $turno->setHora($hora ?? '');
        
        $turno->setServicio($fila['servicio'] ?? '');
        $turno->setEstado($fila['estado'] ?? 'pendiente');
        $turno->setNotas($fila['notas'] ?? null);
        $turno->setCreadoEn($fila['creado_en'] ?? null);
        $turno->setActualizadoEn($fila['actualizado_en'] ?? null);
        
        return $turno;
    }

    /**
     * Convertir un objeto Turno a array
     */
    public function convertirAArray($turno) {
        return [
            'id' => $turno->getId(),
            'nombre' => $turno->getNombre(),
            'email' => $turno->getEmail(),
            'telefono' => $turno->getTelefono(),
            'fecha' => $turno->getFecha(),
            'hora' => $turno->getHora(),
            'servicio' => $turno->getServicio(),
            'estado' => $turno->getEstado(),
            'notas' => $turno->getNotas(),
            'creado_en' => $turno->getCreadoEn(),
            'actualizado_en' => $turno->getActualizadoEn()
        ];
    }
}
?>

<?php

/**
 * Controlador Turno - Maneja todas las peticiones relacionadas con turnos
 */

class TurnoController extends ControladorBase
{
    private $repositorioTurno;

    public function __construct()
    {
        $this->repositorioTurno = new TurnoRepository();
    }

    /**
     * Mostrar la vista principal
     */
    public function index()
    {

        $this->cargarVista('turnos/index');
    }

    /**
     * Obtener todos los turnos (API)
     */
    public function obtenerTodos()
    {
        $filtros = [];

        if (isset($_GET['fecha'])) {
            $filtros['fecha'] = $_GET['fecha'];
        }

        if (isset($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        if (isset($_GET['email'])) {
            $filtros['email'] = $_GET['email'];
        }

        $turnos = $this->repositorioTurno->obtenerTodos($filtros);

        // Convertir objetos Turno a arrays para JSON
        $datos = array_map(function ($turno) {
            return $turno->toArray();
        }, $turnos);

        $this->respuestaJson([
            'exito' => true,
            'datos' => $datos
        ]);
    }

    /**
     * Obtener un turno por ID (API)
     */
    public function obtenerPorId()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'ID de turno requerido'
            ], 400);
        }

        $turno = $this->repositorioTurno->obtenerPorId($id);

        if ($turno) {
            $this->respuestaJson([
                'exito' => true,
                'datos' => $turno->toArray()
            ]);
        } else {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Turno no encontrado'
            ], 404);
        }
    }

    /**
     * Crear un nuevo turno (API)
     */
    public function crear()
    {
        $datos = $this->obtenerDatosPeticion();

        // Validar campos requeridos
        $camposRequeridos = ['nombre', 'email', 'telefono', 'fecha', 'hora', 'servicio'];
        $camposFaltantes = $this->validarCampos($datos, $camposRequeridos);

        if (!empty($camposFaltantes)) {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Campos requeridos faltantes: ' . implode(', ', $camposFaltantes)
            ], 400);
        }

        // Crear objeto Turno desde los datos
        $turno = new Turno($datos);

        // Verificar si el horario está disponible
        if (!$this->repositorioTurno->horarioDisponible($turno->getFecha(), $turno->getHora())) {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'El horario seleccionado no está disponible'
            ], 400);
        }

        // Crear el turno usando el repositorio
        $id = $this->repositorioTurno->crear($turno);

        if ($id) {
            $this->respuestaJson([
                'exito' => true,
                'mensaje' => 'Turno creado exitosamente',
                'id' => $id
            ], 201);
        } else {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Error al crear el turno'
            ], 500);
        }
    }

    /**
     * Actualizar un turno (API)
     */
    public function actualizar()
    {
        $datos = $this->obtenerDatosPeticion();

        if (empty($datos['id'])) {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'ID de turno requerido'
            ], 400);
        }

        // Obtener el turno existente
        $turno = $this->repositorioTurno->obtenerPorId($datos['id']);

        if (!$turno) {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Turno no encontrado'
            ], 404);
        }

        // Actualizar propiedades del turno
        if (isset($datos['nombre'])) $turno->setNombre($datos['nombre']);
        if (isset($datos['email'])) $turno->setEmail($datos['email']);
        if (isset($datos['telefono'])) $turno->setTelefono($datos['telefono']);
        if (isset($datos['fecha'])) $turno->setFecha($datos['fecha']);
        if (isset($datos['hora'])) $turno->setHora($datos['hora']);
        if (isset($datos['servicio'])) $turno->setServicio($datos['servicio']);
        if (isset($datos['estado'])) $turno->setEstado($datos['estado']);
        if (isset($datos['notas'])) $turno->setNotas($datos['notas']);

        // Verificar si el horario está disponible (excluyendo el turno actual)
        if (isset($datos['fecha']) || isset($datos['hora'])) {
            $fecha = $turno->getFecha();
            $hora = $turno->getHora();

            if (!$this->repositorioTurno->horarioDisponible($fecha, $hora, $turno->getId())) {
                $this->respuestaJson([
                    'exito' => false,
                    'mensaje' => 'El horario seleccionado no está disponible'
                ], 400);
            }
        }

        // Actualizar el turno usando el repositorio
        $exito = $this->repositorioTurno->actualizar($turno);

        if ($exito) {
            $this->respuestaJson([
                'exito' => true,
                'mensaje' => 'Turno actualizado exitosamente'
            ]);
        } else {
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Error al actualizar el turno'
            ], 500);
        }
    }

    /**
     * Eliminar un turno (API)
     */
    public function eliminar()
    {
        try {
            $datos = $this->obtenerDatosPeticion();

            // Log de debug solo en desarrollo (usar error_log en lugar de echo)
            if (defined('MODO_DESARROLLO') && MODO_DESARROLLO) {
                error_log('Datos recibidos en eliminar(): ' . print_r($datos, true));
            }

            // Validar que el ID exista y sea válido
            if (!isset($datos['id']) || $datos['id'] === '' || $datos['id'] === null) {
                $this->respuestaJson([
                    'exito' => false,
                    'mensaje' => 'ID de turno requerido',
                    'debug' => defined('MODO_DESARROLLO') && MODO_DESARROLLO ? ['datos_recibidos' => $datos] : null
                ], 400);
                return;
            }

            // Asegurar que el ID sea numérico
            $id = filter_var($datos['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                $this->respuestaJson([
                    'exito' => false,
                    'mensaje' => 'ID de turno inválido',
                    'debug' => defined('MODO_DESARROLLO') && MODO_DESARROLLO ? ['id_recibido' => $datos['id']] : null
                ], 400);
                return;
            }

            $exito = $this->repositorioTurno->eliminar($id);

            if ($exito) {
                $this->respuestaJson([
                    'exito' => true,
                    'mensaje' => 'Turno eliminado exitosamente'
                ]);
            } else {
                $this->respuestaJson([
                    'exito' => false,
                    'mensaje' => 'Error al eliminar el turno. El turno puede no existir o ya haber sido eliminado.'
                ], 500);
            }
        } catch (Exception $e) {
            error_log('Error en eliminar(): ' . $e->getMessage());
            $this->respuestaJson([
                'exito' => false,
                'mensaje' => 'Error al eliminar el turno',
                'debug' => defined('MODO_DESARROLLO') && MODO_DESARROLLO ? ['error' => $e->getMessage()] : null
            ], 500);
        }
    }
}

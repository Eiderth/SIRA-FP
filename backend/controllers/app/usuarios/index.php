<?php

class Usuarios_controller {
    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo; 
    }

    public function init($action, $input) {

        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SESSION['rol'] !== 'Administrador' || $this->modelo->type !== 'Administrador') {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Acceso denegado.']);
            return;
        }
            
        switch ($action) {
            case 'listar':
                $this->listar();
                break;

            case 'crear':
                $this->crear($input);
                break;

            case 'eliminar':
                $this->eliminar($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(['estado' => 'error', 'mensaje' => 'Acción no válida']);
                break;
        }
    }

    private function listar() {
        try {
            $usuarios = $this->modelo->listar_usuarios();
            echo json_encode(['estado' => 'completado', 'usuarios' => $usuarios]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error al cargar la lista']);
        }
    }

    private function crear($data) {
        $nombre = trim($data['nombre'] ?? '');
        $pass   = trim($data['pass'] ?? '');
        $rol    = trim($data['rol'] ?? '');

        if (empty($nombre) || empty($pass) || empty($rol)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Todos los campos son obligatorios']);
            return;
        }

        try {

            if ($this->modelo->existe('USUARIO', 'nombre', $nombre)) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'El nombre de usuario ya existe']);
                return;
            }

            $id_nuevo = $this->modelo->crear_usuario($nombre, $pass, $rol);

            echo json_encode([
                'estado'  => 'completado', 
                'mensaje' => 'Usuario creado correctamente', 
                'Usuario' => [
                    'id'     => $id_nuevo,
                    'nombre' => $nombre,
                    'rol'    => $rol
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error interno al guardar']);
        }
    }

    private function eliminar($data) {

        $id = (int) $data['id'];

        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de usuario inválido']);
            return;
        }

        if ($id == ($_SESSION['usuario_id'] ?? 0)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'No puedes eliminar tu propio usuario en sesión']);
            return;
        }

        try {
            $ejecutado = $this->modelo->eliminar_usuario($id);

            if ($ejecutado) {
                echo json_encode([
                    'estado' => 'completado',
                    'mensaje' => 'Usuario eliminado correctamente'
                ]);
            } else {
                echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo eliminar el usuario o no existe']);
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error de base de datos al eliminar el usuario']);
        }
    }
}
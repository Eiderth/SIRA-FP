<?php

class Usuarios_controller {
    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo; 
    }

    public function init($action, $input) {

        if ($this->modelo->type !== 'Administrador') {
            echo json_encode(['estado' => 'error', 'error' => 'no_autorizado']);
            return;
        }
            
        switch ($action) {
            case 'listar_usuarios':
                $this->listar_usuarios();
                break;

            case 'crear_usuario':
                $this->crear_usuario($input);
                break;

            case 'eliminar_usuario':
                $this->eliminar_usuario($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(['estado' => 'error', 'error' => 'accion_no_encontrada']);
                break;
        }
    }

    private function listar_usuarios() {
    
        $usuarios = $this->modelo->listar_usuarios();
        http_response_code(200);     
        echo json_encode(['estado' => 'completado', 'usuarios' => $usuarios]);
    
    }

    private function crear_usuario($input) {

        if (empty($input['nombre']) || empty($input['pass']) || empty($input['rol'])) {
            echo json_encode(['estado' => 'error', 'error' => 'campos_incompletos']);
            return;
        }

        try {

            if ($this->modelo->existe('USUARIO', 'nombre', $input['nombre'])) {
                echo json_encode(['estado' => 'error', 'error' => 'usuario_ya_registrado']);
                return;
            }

            $id_nuevo = $this->modelo->crear_usuario($input['nombre'], $input['pass'], $input['rol']);

            echo json_encode([
                'estado'  => 'completado', 
                'usuario' => [
                    'id'     => $id_nuevo,
                    'nombre' => $input['nombre'],
                    'rol'    => $input['rol']
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'error_en_db']);
        }
    }

    private function eliminar_usuario($input) {

        $id = (int) $input['id'];

        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'error' => 'credenciales_invalidas']);
            return;
        }

        if ($id == $this->modelo->id) {
            echo json_encode(['estado' => 'error', 'error' => 'no_autorizado']);
            return;
        }

        try {
            $eliminado = $this->modelo->eliminar_usuario($id);

            if ($eliminado) {
                echo json_encode([
                    'estado' => 'completado',
                    'eliminado' => $eliminado
                ]);
            } else {
                echo json_encode(['estado' => 'error', 'error' => 'error_al_eliminar']);
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'error_en_db']);
        }
    }
}
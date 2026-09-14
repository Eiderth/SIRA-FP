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

            case 'filtrar_usuarios':
                $this->filtrar_usuarios($input);
                break;

            case 'crear_usuario':
                $this->crear_usuario($input);
                break;

            case 'actualizar_usuario':
                $this->actualizar_usuario($input);
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

    private function filtrar_usuarios($input) {
        
        if (empty(array_filter(($input)))) {
            http_response_code(200);
            echo json_encode(['estado' => 'completado', 'usuario' => []]);
            return;
        } 

        $usuarios = $this->modelo->filtrar_usuarios($input['general'], $input['rol'], $input['estado']);
        http_response_code(200);     
        echo json_encode(['estado' => 'completado', 'usuarios' => $usuarios]);
    
    }


    private function crear_usuario($input) {

        if (empty(array_filter($input))) {

            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'campos_incompletos']);
            return;
        
        } else if($input['pass'] !== $input['confirmar_pass']) {

            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'datos_invalidos']);
        
        }

        try {

            if ($this->modelo->existe('USUARIO', 'nombre', $input['nombre'])) {
                echo json_encode(['estado' => 'error', 'error' => 'usuario_ya_registrado']);
                return;
            }

            $id = $this->modelo->crear_usuario($input['nombre'], $input['pass'], $input['rol']);

            if ($id) {
                http_response_code(201);
                echo json_encode([
                    'estado'  => 'completado', 
                    'creado' => true
                ]);
            }else {
                http_response_code(500);
                echo json_encode([
                    'estado'  => 'error', 
                    'error' => 'error_al_crear_usuario'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'error_en_db']);
        }
    }

    private function actualizar_usuario($input) {
        
        if (empty(array_filter($input))) {

            http_response_code(201);
            echo json_encode(['estado' => 'completado', 'actualizado' => true]);
            return;

        } else if($input['rol'] !== 'Administrador' && $input['rol'] !== 'Usuario' && $input['rol'] !== 'Secretaria') {
            
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'datos_invalidos']);
            return;
            
        } else if($input['estado'] !== 'Activo' && $input['estado'] !== 'Inactivo') {
            
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'datos_invalidos']);
            return;

        }

        if ($this->modelo->id == $input['id'] && ($input['rol'] !== 'Administrador') ) {
            http_response_code(401);
            echo json_encode(['estado' => 'error', 'error' => 'no_autorizado']);
            return;
        }

      
        try {

            $actualizado = $this->modelo->actualizar_usuario($input['nombre'], $input['rol'], $input['estado'], $input['id']);

            if ($actualizado) {
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'actualizado' => true]);
            } else {
                http_response_code(401);
                echo json_encode(['estado' => 'error', 'error' => 'no_autorizado']);
            } 

        } catch(PDOException $e) {

            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'error_en_db']);
             
        }
        
    }

    private function eliminar_usuario($input) {

        $id = (int) $input['id'];

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'credenciales_invalidas']);
            return;
        }
  
        if ($id == $this->modelo->id) {
            http_response_code(401);

            echo json_encode(['estado' => 'error', 'error' => 'no_autorizado']);
            return;
        }

        try {

            $eliminado = $this->modelo->eliminar_usuario($id);

            if ($eliminado) {
                http_response_code(201);
                echo json_encode([
                    'estado' => 'completado',
                    'eliminado' => $eliminado
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'error_al_eliminar_usuario']);
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'error_en_db']);
        }
    }
}
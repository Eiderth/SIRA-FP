<?php

class Usuarios_controller {
    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo; 
    }

    public function init($action, $input) {

        if ($this->modelo->type !== 'Administrador') {
            echo json_encode(['estado' => 'error', 'error' => 'No autorizado para realizar esta acción']);
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
                echo json_encode(['estado' => 'error', 'error' => 'La acción solicitada no fue encontrada']);
                break;
        }
    }

    private function listar_usuarios() {
    
        http_response_code(200);     
        echo json_encode([
            'estado' => 'completado', 
            'usuarios' => $this->modelo->listar_usuarios()
        ]);
    
    }

    private function filtrar_usuarios($input) {
        
        if (empty($input['general']) && empty( $input['rol']) && empty($input['estado']) ) {
            http_response_code(200);
            echo json_encode([
                'estado' => 'completado', 
                'usuario' => $this->modelo->listar_usuarios()
            ]);
            return;
        } 

        http_response_code(200);     
        echo json_encode([
            'estado' => 'completado',
            'usuarios' => $this->modelo->filtrar_usuarios($input['general'], $input['rol'], $input['estado'])
        ]);
    
    }


    private function crear_usuario($input) {

        if (empty($input['nombre']) || 
            empty($input['pass']) || 
            empty($input['rol']) || 
            empty($input['confirmar_pass'])
        ) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, complete todos los campos obligatorios']);
            return;
        
        } else if($input['pass'] !== $input['confirmar_pass']) {

            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Las contraseñas no coinciden']);
        
        } else if ($this->modelo->existe('USUARIO', 'nombre', $input['nombre'])) {
            
            echo json_encode(['estado' => 'error', 'error' => 'El nombre de usuario ya se encuentra registrado']);
            return;

        }

        try {

            $id = $this->modelo->crear_usuario($input['nombre'], $input['pass'], $input['rol']);

            if (!empty($id)) {
                http_response_code(201);
                echo json_encode([
                    'estado'  => 'completado', 
                    'creado' => true
                ]);
            }else {
                http_response_code(500);
                echo json_encode([
                    'estado'  => 'error', 
                    'error' => 'No se pudo crear el usuario'
                ]);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        }
    }

    private function actualizar_usuario($input) {
        
        if (empty($input['id'])) {

            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, indique el ID del usuario']);
            return;

        } else if (empty($input['nombre']) && empty($input['rol']) && empty($input['estado'])) {

            http_response_code(201);
            echo json_encode(['estado' => 'completado', 'actualizado' => true]);
            return;

        } else if($input['rol'] !== 'Administrador' && $input['rol'] !== 'Usuario' && $input['rol'] !== 'Secretaria') {
            
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'El rol seleccionado no es válido']);
            return;
            
        } else if($input['estado'] !== 'Activo' && $input['estado'] !== 'Inactivo') {
            
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'El estado seleccionado no es válido']);
            return;

        }

        if ($this->modelo->id == $input['id'] && ($input['rol'] !== 'Administrador') ) {
            http_response_code(401);
            echo json_encode(['estado' => 'error', 'error' => 'No puedes quitarte los privilegios de administrador a ti mismo']);
            return;
        }

      
        try {

            $actualizado = $this->modelo->actualizar_usuario($input['nombre'], $input['rol'], $input['estado'], $input['id']);

            if ($actualizado) {
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'actualizado' => true]);
            } else {
                http_response_code(401);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo actualizar el usuario']);
            } 

        } catch(PDOException $e) {

            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
             
        }
        
    }

    private function eliminar_usuario($input) {

        if (empty($input['id'])) {
        
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, indique el usuario a eliminar']);
            return;
        
        }

        $id = (int) $input['id'];

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'El ID proporcionado no es válido']);
            return;
        }
  
        if ($id == $this->modelo->id) {
            http_response_code(401);

            echo json_encode(['estado' => 'error', 'error' => 'No puedes eliminar tu propio usuario en sesión']);
            return;
        }

        try {

            $eliminado = $this->modelo->eliminar_usuario($id);

            if (!empty($eliminado)) {
                http_response_code(201);
                echo json_encode([
                    'estado' => 'completado',
                    'eliminado' => $eliminado
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar el usuario']);
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        }
    }
}
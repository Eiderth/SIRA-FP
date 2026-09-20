<?php

class Login_controller {

    private $sistema;

    public function __construct($sistema) {
        $this->sistema = $sistema;
    }

    public function init($action, $input) {

        switch ($action) {
            case 'existen_usuarios':
                $this->existen_usuarios($input);
                break;

            case 'crear_cuenta':
                $this->crear_cuenta($input);
                break;

            case 'iniciar_sesion':
                $this->iniciar_sesion($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(["estado" => "error", "error" => "La acción solicitada no fue encontrada"]);
                break;

        }
    }

    private function existen_usuarios() {
        $existen_usuarios = $this->sistema->existe_registro_usuario();
        http_response_code(200);
        echo json_encode(['estado' => 'completado', 'existen_usuarios' => $existen_usuarios]);
    }

    private function crear_cuenta($input) {
            
        if (empty($input['nombre']) || empty($input['pass']) || empty($input['confirmar_pass'])) {
        
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, complete todos los campos obligatorios']);
            return;
        
        } else if($input['pass'] !== $input['confirmar_pass']) {

            http_response_code(401);
            echo json_encode(['estado' => 'error', 'error' => 'Las contraseñas no coinciden']);
            return;

        }

        try {
            $admin_id = $this->sistema->crear_primer_admin($input['nombre'], $input['pass']);
            
            if ($admin_id) {
                $usuario = [
                    'id'    => $admin_id,
                    'nombre' => $input['nombre'],
                    'rol'    => 'Administrador',
                ];

                http_response_code(201);
                echo json_encode([
                    'estado' => 'completado',
                    'usuario' => $usuario,
                    'acceso' => true 
                ]);

            } else {
                http_response_code(401);
                echo json_encode(['estado' => 'error', 'acceso' => false, 'error' => 'No se pudo crear la cuenta de administrador']);
            }
            
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'acceso' => false, 'error' => 'Ocurrió un error en la base de datos']);
        } 
    }

    private function iniciar_sesion($input) {

        if (empty($input['nombre']) || empty($input['pass'])) {

            http_response_code(400);
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, ingrese su usuario y contraseña']);
            return;
            
        }
            
        $usuario_encontrado = $this->sistema->buscar_usuario($input['nombre'], $input['pass']) ?? null;
        
        if ($usuario_encontrado) {
            
            http_response_code(200); 
            echo json_encode([
                'estado'  => 'completado',
                'acceso'  => true,
                'usuario' => $usuario_encontrado
            ]);

        } else {

            http_response_code(404);
            echo json_encode([
                'estado'  => 'error',
                'acceso'  => false,
                'error' => 'El usuario o la contraseña son incorrectos',
            ]);
        }
    }
}
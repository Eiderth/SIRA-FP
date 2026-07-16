<?php

class Login_controller {

    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo;
    }

    public function init($action, $input) {

        switch ($action) {

            case 'iniciar_sesion':
                $this->iniciar_sesion($input);
                break;

            case 'cerrar_sesion':
                $this->cerrar_sesion();
                break;

            default:
                http_response_code(404);
                echo json_encode(["estado" => "error", "mensaje" => "Acción no encontrada en el Login"]);
                break;

        }
    }

    private function iniciar_sesion($data) {

        if (empty($data['nombre']) || empty($data['pass'])) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Debe llenar ambos campos']);
            return;
        }

        try {
            $usuario_encontrado = $this->modelo->buscar_usuario($data['nombre'], $data['pass']) ?? null;
            
            $response = null;

            if ($usuario_encontrado) {
                $response = $usuario_encontrado;
            } else {

                $admin_id = $this->modelo->crear_primer_admin($data['nombre'], $data['pass']);
                if ($admin_id) {
                    $response = [
                        'id'     => $admin_id,
                        'nombre' => $data['nombre'],
                        'rol'    => 'Administrador'
                    ];
                }
            }

            if ($response) {

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_id'] = $response['id'];
                $_SESSION['nombre'] = $response['nombre'];
                $_SESSION['rol'] = $response['rol'];

                echo json_encode([
                    'estado'  => 'completado',
                    'acceso'  => true,
                    'mensaje' => 'Bienvenido',
                    'usuario' =>  $response
                ]);

            } else {
                echo json_encode([
                    'estado'  => 'completado',
                    'acceso'  => false,
                    'mensaje' => 'Usuario o contraseña incorrectos',
                ]);
            }

        } catch (PDOException $e) {
            
            echo json_encode([
                'estado'  => 'error', 
                'mensaje' => 'Error interno del servidor en el proceso de Login', 
                'error'   => $e->getMessage()
            ]);
        }
    }

    private function cerrar_sesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        echo json_encode([
            'estado'  => 'completado',
            'mensaje' => 'Sesión cerrada correctamente'
        ]);
    }
}
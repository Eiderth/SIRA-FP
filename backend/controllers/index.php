<?php
require_once __DIR__ . '/./app/dashboard/index.php';
require_once __DIR__ . '/./login/index.php';
require_once __DIR__ . '/./app/inscripcion/index.php';
require_once __DIR__ . '/./app/busqueda/index.php';
require_once __DIR__ . '/./app/reportes/index.php';
require_once __DIR__ . '/./app/grados-secciones/index.php';
require_once __DIR__ . '/./app/usuarios/index.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../models/Sistema.php';
require_once __DIR__ . '/../models/Administrador.php';
require_once __DIR__ . '/../models/Usuario.php';


class Router {

    private $sistema;
    private $usuario; //puede ser admin o usuario comun
    
    public function __construct() {
         
        $this->sistema = new Sistema(Database::get_instance());

    }

    public function run($controlador, $action, $input) {

        // limpiar la entrada
        array_walk_recursive($input, function(&$item){
            if(is_string($item)) {
                $item = trim($item);
            } 
        });
        
        try {

            $usuario_session = $this->sistema->verificar_sesion();
            
            if ($controlador == 'router') {
                switch ($action) {
                    case 'validar_session':
                        http_response_code(200);
                        echo json_encode([
                            'estado' => 'completado', 
                            'acceso' => !empty($usuario_session), 
                            'usuario' => $usuario_session, 
                        ]);
                        break;

                    case 'cerrar_session':

                        $cerrada = $this->sistema->cerrar_sesion();

                        if ($cerrada) {       
                            http_response_code(200);
                            echo json_encode([
                                'estado'  => 'completado',
                                'cerrada' => $cerrada
                            ]);

                        } else {
                            http_response_code(500);
                            echo json_encode([
                                'estado'  => 'error',
                                'cerrada' => false,
                                'error' => 'error_cerrar_session'
                            ]);
                        }
                        break;  
                }

                return;
            }

            if( $controlador == 'login_controller') {
                $login_controller = new Login_controller($this->sistema); 
                $login_controller->init($action, $input);
                return;
            }    

            if(empty($usuario_session)) {
                http_response_code(401);
                echo json_encode([
                    'estado' => 'error', 
                    'error' => 'session_expirada',
                ]);
                return;
            }

            if ($usuario_session['rol'] == 'Administrador') {
            
                $this->usuario = new Administrador(Database::get_instance(), $usuario_session['id']);
            
            } else {

                $this->usuario = new Usuario(Database::get_instance(), $usuario_session['id']);
                
            }

            switch ($controlador) {
                case 'dashboard_controller':
                    $dashboard_controller = new Dashboard_controller($this->usuario);
                    $dashboard_controller->init($action, $input);
                    break;
                    
                case 'inscripcion_controller':
                    $inscripcion_controller = new Inscripcion_controller($this->usuario, $this->sistema);
                    $inscripcion_controller->init($action, $input);
                    break;

                case 'busqueda_controller':
                    $busqueda_controller = new Busqueda_controller($this->usuario, $this->sistema);
                    $busqueda_controller->init($action, $input);
                    break;

                case 'reportes_controller':
                    $reportes_controller = new Reportes_controller($this->usuario);
                    $reportes_controller->init($action, $input);
                    break;

                case 'grados_secciones_controller':
                    $grados_secciones_controller = new Grados_secciones_controller($this->usuario, $this->sistema);
                    $grados_secciones_controller->init($action, $input);
                    break;

                
                case 'usuarios_controller':

                    if ($usuario_session['rol'] !== 'Administrador') {
                        http_response_code(401);
                        echo json_encode(['estado' == 'error', 'error' => 'no_autorizado']);
                        break;
                    }

                    $usuarios_controller = new Usuarios_controller($this->usuario);
                    $usuarios_controller->init($action, $input);
                    break;

                default:
                    echo json_encode(['estado' => 'error', 'error' => 'accion_no_encontrada']);
                    break;
            }
            
        } catch (PDOException $e) {
            http_response_code(404);
            echo json_encode(['estado' => 'error', 'error' => "error_servidor"]);
            var_dump($e->getMessage());//borrar para produccion
        }
    }
}
<?php

require_once __DIR__ . '/./app/dashboard/index.php';
require_once __DIR__ . '/./login/index.php';
require_once __DIR__ . '/./app/inscripcion/index.php';
require_once __DIR__ . '/./app/busqueda/index.php';
require_once __DIR__ . '/./app/reportes/index.php';
require_once __DIR__ . '/./app/academicos/index.php';
require_once __DIR__ . '/./app/usuarios/index.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../models/Sistema.php';
require_once __DIR__ . '/../models/Administrador.php';

foreach(glob(__DIR__ . '/../models/services/*.php') as $archivo) {
    require_once $archivo;
}


class Router {

    private $sistema;
    
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

            $usuario_session = $this->sistema->validar_sesion();
            
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

                        try {

                            $this->sistema->cerrar_sesion();
                            http_response_code(200);
                            echo json_encode([
                                'estado'  => 'completado',
                                'cerrada' => true
                            ]);

                        } catch (PDOException $e) {
                            
                            http_response_code(500);
                            echo json_encode([
                                'estado'  => 'error',
                                'error' => 'error al cerros session, intente de nuevo'
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
                    'estado' => 'sessión-expirada', 
                ]);
                return;
            }

            // if ($usuario_session['rol'] == 'Administrador') {
            
            //     $this->usuario = new Administrador(Database::get_instance(), $usuario_session['id']);
            
            // } else {

                // $this->usuario = new Usuario(Database::get_instance(), $usuario_session['id']);
                
            // }

            switch ($controlador) {
                
                case 'dashboard_controller':
                    $dashboard_model = new Dashboard_model(Database::get_instance());
                    $dashboard_controller = new Dashboard_controller($dashboard_model);
                    $dashboard_controller->init($action, $input);
                    break;
                    
                case 'inscripcion_controller':
                    $inscripcion_model = new Inscripcion_model(Database::get_instance()) ;
                    $inscripcion_controller = new Inscripcion_controller($inscripcion_model);
                    $inscripcion_controller->init($action, $input);
                    break;

                case 'busqueda_controller': 
                    $busqueda_model = new busqueda_model(Database::get_instance());
                    $busqueda_controller = new Busqueda_controller($busqueda_model);
                    $busqueda_controller->init($action, $input);
                    break;

                case 'reportes_controller':
                    $reportes_model = new Reporte_model(Database::get_instance());
                    $reportes_controller = new Reportes_controller($reportes_model);
                    $reportes_controller->init($action, $input);
                    break;

                case 'academicos_controller':
                    $academicos_model = new academicos_model(Database::get_instance());
                    $grados_secciones_controller = new academicos_controller($academicos_model);
                    $grados_secciones_controller->init($action, $input);
                    break;

                
                case 'usuarios_controller':

                    if ($usuario_session['rol'] !== 'Administrador') {
                        http_response_code(401);
                        echo json_encode(['estado' == 'error', 'error' => 'no_autorizado']);
                        break;
                    }
                    
                    $admin_model = new Administrador(Database::get_instance(), $usuario_session['id']);
                    $usuarios_controller = new Usuarios_controller($admin_model);
                    $usuarios_controller->init($action, $input);
                    break;

                default:
                    echo json_encode(['estado' => 'error', 'error' => 'accion no encontrada']);
                    break;
            }
            
        } catch (PDOException $e) {
            http_response_code(404);
            echo json_encode(['estado' => 'error', 'error' => 'error_servidor', 'e' => $e->getMessage()]);
            // var_dump($e->getMessage());
        }
    }
}
<?php
require_once __DIR__ . '/./app/dashboard/index.php';
require_once __DIR__ . '/./login/index.php';
require_once __DIR__ . '/./app/inscripcion/index.php';
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
        
        try {
            if($controlador == 'login_controller') {
                $login_controller = new Login_controller($this->sistema); 
                $login_controller->init($action, $input);
                return;
            }    

            if (session_status() === PHP_SESSION_NONE) session_start();

            $this->usuario = $_SESSION['rol'] == 'Administrador' ? new Administrador(Database::get_instance()) : $this->usuario = new Usuario(Database::get_instance());

            switch ($controlador) {
                case 'dashboard_controller':
                    $dashboard_controller = new Dashboard_controller($this->usuario);
                    $dashboard_controller->init($action, $input);
                    break;
                    
                case 'inscripcion_controller':
                    $inscripcion_controller = new Inscripcion_controller($this->usuario, $this->sistema);
                    $inscripcion_controller->init($action, $input);
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
                    $usuarios_controller = new Usuarios_controller($this->usuario);
                    $usuarios_controller->init($action, $input);
                    break;
                default:
                    echo json_encode(['estado' => 'error', 'mensaje' => 'ruta no encontrada']);
                    break;
            }
            
        } catch (PDOException $e) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error interno del servidor']);
        }
    }
}
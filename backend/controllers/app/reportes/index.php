<?php

class Reportes_controller {
    
    private $modelo;

    function __construct($modelo){
        $this->modelo = $modelo;
    }

    public function init($action, $input) {

        switch ($action) {

            case 'listar':
                $this->listar($input);
                break;

            case 'detalles_planilla':
                $id = $_GET['id'] ?? 0;
                $this->detalles_planilla($id);
                break;

            default:
                http_response_code(404);
                echo json_encode(["estado" => "error", "mensaje" => "Acción no encontrada en reportes"]);
                break;

        }
    }

    private function listar($input){
        try {

            $inscritos = $this->modelo->listar_estudiantes();
            
            echo json_encode(['estado' => 'completado', 'datos' => $inscritos]);
        
        } catch (PDOException $e) {
        
            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        
        }        
    }

    private function detalles_planilla($id){

        try {

            $inscripcion = $this->modelo->buscar_planilla_inscripcion($id);

            if ($inscripcion) {
            
                $representante = $this->modelo->buscar_todo('representantes', 'cedula', $inscripcion['representante_principal_cedula']);

                echo json_encode([
                    'estado' => 'completado',
                    'datos' => [
                        'inscripcion' => $inscripcion,
                        'representante_principal' => $representante
                    ]  
                ]);

            } else {

                echo json_encode(['estado' => 'error', 'mensaje' => 'No encontrado']);
            }

        } catch (PDOException $e) {

            echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        
        }
    }

}
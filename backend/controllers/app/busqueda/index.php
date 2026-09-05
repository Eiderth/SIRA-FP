<?php

class Busqueda_controller {

    private $modelo;
    private $sistema;

    public function __construct($modelo, $sistema) {
        $this->modelo = $modelo;
        $this->sistema = $sistema; 
    }

    public function init($action, $input) {

        switch ($action) {
            case 'traer_parametros':
            $this->traer_parametros();
            break;

            case 'buscar_coincidencias': 
            $this->buscar_coincidencias($input);
            break;

            case 'buscar_datos_estudiante': 
            $this->buscar_datos_estudiante($input);
            break;

            default: 
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'acción no válida']);
            break;
        }
    }

    private function traer_parametros() {
        $periodos =  $this->sistema->obtener_periodos_academicos();
        $grados =  $this->sistema->obtener_grados();
        $secciones =  $this->sistema->obtener_secciones();

        echo json_encode([
            'estado' => 'completado',
            'data' => [
                'periodos' => $periodos,
                'grados' => $grados,
                'secciones' => $secciones
            ]
        ]);
    }

    private function buscar_coincidencias($input) {
        $resultados = [];
        if (!empty(array_filter($input) )) {
            $resultados['estudiantes'] = $this->sistema->buscar_coincidencias($input);
        } 
        echo json_encode([
            'estado' => 'completado', 
            'resultados' => $resultados
        ]);
    }

    private function buscar_datos_estudiante($input) {
        $resultados = [];
        if (!empty(array_filter($input) )) {
            $resultados = $this->sistema->buscar_historial_estudiante($input['estudiante_id']);
        } 
        echo json_encode([
            'estado' => 'completado', 
            'resultados' => $resultados
        ]);
    }


    
}
<?php

class Busqueda_controller {

    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo;
    }

    public function init($action, $input) {

        switch ($action) {
            case 'traer_parametros':
            $this->traer_parametros();
            break;

            case 'buscar_coincidencias': 
            $this->buscar_coincidencias($input);
            break;

            case 'obtener_datos_estudiante': 
            $this->obtener_datos_estudiante($input);
            break;

            default: 
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'acción no válida']);
            break;
        }
    }

    private function traer_parametros() {
        $periodos =  $this->modelo->obtener_periodos_academicos();
        $grados =  $this->modelo->obtener_grados();
        $secciones =  $this->modelo->obtener_secciones();

        echo json_encode([
            'estado' => 'completado',
            'periodos' => $periodos,
            'grados' => $grados,
            'secciones' => $secciones
        ]);
    }

    private function buscar_coincidencias($input) {
        $pagina = !empty($input['pagina']) ? (int)$input['pagina'] : 1;
        $limite = 10;
        $estudiantes = $this->modelo->buscar_coincidencias_estudiante($input, $pagina, $limite);
        $total_registros = $this->modelo->contar_coincidencias_estudiante($input);

        $total_paginas = ceil($total_registros / $limite);

        http_response_code(200);
        echo json_encode([
            'estado' => 'completado',
            'estudiantes' => $estudiantes,
            'total' => $total_registros,
            'total_paginas' => max(1, (int)$total_paginas),
            'pagina_actual' => $pagina
            
        ]);
    }

    private function obtener_datos_estudiante($input) {
        $resultados = [];
        
        if (!empty(array_filter($input))) {
            $datos_estudiante = $this->modelo->obtener_datos_estudiante('id', $input['estudiante_id']);
            
            if ($datos_estudiante) {
                $rep_principal_id = $datos_estudiante['representante_principal_id'] ?? null;
                $rep_secundario_id = $datos_estudiante['representante_secundario_id'] ?? null;

                unset($datos_estudiante['representante_principal_id'], $datos_estudiante['representante_secundario_id']);

                $resultados['estudiante'] = $datos_estudiante;

                if (!empty($rep_principal_id)) {
                    $resultados['representante_principal'] = $this->modelo->obtener_representante_completo('id',$rep_principal_id);
                }

                if (!empty($rep_secundario_id)) {
                    $resultados['representante_secundario'] = $this->modelo->obtener_representante_completo('id',$rep_secundario_id);
                }
            }
        } 

        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            ...$resultados
        ]);
    }
    

    
}
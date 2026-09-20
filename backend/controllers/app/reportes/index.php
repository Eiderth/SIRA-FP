<?php

class Reportes_controller {
    
    private $modelo;
    private $sistema;


    function __construct($modelo, $sistema){
        $this->modelo = $modelo;
        $this->sistema = $sistema;
    }

    public function init($action, $input) {

        switch ($action) {
            
            case 'traer_parametros_filtros':
                $this->traer_parametros_filtros();
                break;

            case 'filtrar_reportes':
                $this->filtrar_reportes($input);
                break;

            case 'detalles_planilla':
                $id = $_GET['id'] ?? 0;
                $this->detalles_planilla($id);
                break;

            case 'exportar_reportes':
                $this->exportar_reportes($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(["estado" => "error", "error" => "La acción solicitada no fue encontrada en reportes"]);
                break;

        }
    }

    private function traer_parametros_filtros(){
     
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado',
            'periodos_academicos' => $this->sistema->obtener_periodos_academicos(),
            'grados' => $this->sistema->obtener_grados(),
            'secciones' => $this->sistema->obtener_secciones()
        ]);

    }

    private function filtrar_reportes($input) {

        $periodo_id = !empty($input['periodo_id']) ? $input['periodo_id'] : null;
        $grado_id   = !empty($input['grado_id'])   ? $input['grado_id']   : null;
        $seccion_id = !empty($input['seccion_id']) ? $input['seccion_id'] : null;
        $pagina = !empty($input['pagina']) ? (int)$input['pagina'] : 1;
        $limite = 10;

        $inscripciones = $this->modelo->filtrar_inscripciones($periodo_id, $grado_id, $seccion_id, $pagina, $limite);
        $total_registros = $this->modelo->contar_total_inscripciones($periodo_id, $grado_id, $seccion_id);

        $total_paginas = ceil($total_registros / $limite);

        http_response_code(200);
        echo json_encode([
            'estado'        => 'completado',
            'inscripciones' => $inscripciones,
            'paginacion'    => [
                'pagina_actual'   => $pagina,
                'limite'          => $limite,
                'total_registros' => $total_registros,
                'total_paginas'   => max(1, (int)$total_paginas)
            ]
        ]);
        
    }

    private function detalles_planilla($id){

        try {

            $inscripcion = $this->modelo->buscar_planilla_inscripcion($id);

            if ($inscripcion) {
            
                $parentesco = $this->modelo->buscar_valor('parentesco', 'PERSONA_REPRESENTANTE', 'id', $inscripcion['representante_principal_id']);
                $persona_id = $this->modelo->buscar_valor('PERSONA_id', 'PERSONA_REPRESENTANTE', 'id', $inscripcion['representante_principal_id']);

                $persona = $this->modelo->buscar_todo('PERSONA', 'id', $persona_id);

                http_response_code(200);
                echo json_encode([
                    'estado' => 'completado',
                    'datos' => [
                        'inscripcion' => $inscripcion,
                        'representante_principal' => [
                            'parentesco' => $parentesco,
                            ...$persona
                        ]
                    ]  
                ]);

            } else {

                http_response_code(404);
                echo json_encode(['estado' => 'error', 'error' => 'No se encontró la planilla de inscripción solicitada']);
            }

        } catch (PDOException $e) {

            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    }

    private function exportar_reportes($input) {
        $periodo_id = !empty($input['periodo_id']) ? $input['periodo_id'] : null;
        $grado_id  = !empty($input['grado_id'])  ? $input['grado_id']  : null;
        $seccion_id = !empty($input['seccion_id']) ? $input['seccion_id'] : null;

        $inscripciones = $this->modelo->filtrar_todas_las_inscripciones($periodo_id, $grado_id, $seccion_id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_inscritos_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Cédula / Escolar', 'Nombres', 'Apellidos', 'Período Académico', 'Grado', 'Sección', 'Estado'], ';');

        foreach ($inscripciones as $i) {
            $doc = $i['cedula_identidad'] ? "{$i['nacionalidad']}-{$i['cedula_identidad']}" : ($i['cedula_escolar'] ?? 'N/P');
            
            fputcsv($output, [
                $doc,
                $i['nombre_1'] . ' ' . ($i['nombre_2'] ?? ''),
                $i['apellido_1'] . ' ' . ($i['apellido_2'] ?? ''),
                $i['periodo'] ?? 'N/P',
                $i['grado'] ?? 'N/P',
                $i['seccion'] ?? 'N/P',
                $i['estado'] ?? 'Inscrito'
            ], ';');
        }

        fclose($output);
        exit;
    }

}
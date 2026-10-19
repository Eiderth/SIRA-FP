<?php

class Reportes_controller {
    
    private $modelo;

    function __construct($modelo){
        $this->modelo = $modelo;
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
    
    public function traer_parametros_filtros() {
     
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado',
            'periodos_academicos' => $this->modelo->obtener_periodos_academicos(),
            'grados' => $this->modelo->obtener_grados(),
            'secciones' => $this->modelo->obtener_secciones(),
            'estados' => $this->modelo->obtener_estados(),
            'municipios' => $this->modelo->obtener_municipios(),
            'parroquias' => $this->modelo->obtener_parroquias(),
            'profesiones' => $this->modelo->obtener_profesiones() 
        ]);
    }
    public function filtrar_reportes($input) {
        
        $pagina = !empty($input['pagina']) ? (int)$input['pagina'] : 1;
        $limite = 10;

        $filtros = [
            'periodo_id'        => !empty($input['periodo_id']) ? $input['periodo_id'] : null,
            'grado_id'          => !empty($input['grado_id']) ? $input['grado_id'] : null,
            'seccion_id'        => !empty($input['seccion_id']) ? $input['seccion_id'] : null,
            'tipo_ingreso'      => !empty($input['tipo_ingreso']) ? $input['tipo_ingreso'] : null,
            
            // Ubicación
            'estado_id'         => !empty($input['estado_id']) ? $input['estado_id'] : null,
            'municipio_id'      => !empty($input['municipio_id']) ? $input['municipio_id'] : null,
            'parroquia_id'      => !empty($input['parroquia_id']) ? $input['parroquia_id'] : null,
            
            // Salud y Antropometría
            'atencion_especial' => !empty($input['atencion_especial']) ? $input['atencion_especial'] : null,
            'posee_alergias'    => !empty($input['posee_alergias']) ? $input['posee_alergias'] : null,
            'enfermedad'        => !empty($input['enfermedad']) ? trim($input['enfermedad']) : null,
            
            // Socioeconómico y Recursos
            'profesion'         => !empty($input['profesion']) ? $input['profesion'] : null,
            'rango_ingreso'     => !empty($input['rango_ingreso']) ? $input['rango_ingreso'] : null,
            'estado_canaima'    => !empty($input['estado_canaima']) ? $input['estado_canaima'] : null,
            'realiza_deportes'  => !empty($input['realiza_deportes']) ? $input['realiza_deportes'] : null,
        ];
        $inscripciones = $this->modelo->filtrar_inscripciones($filtros, $pagina, $limite);
        $total_registros = $this->modelo->contar_total_inscripciones($filtros);

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

  private function detalles_planilla($id) {
        try {
            $registro = $this->modelo->buscar_planilla_inscripcion($id);

            if ($registro) {
                http_response_code(200);
                echo json_encode([
                    'estado' => 'completado',
                    'datos' => [
                        'inscripcion' => $registro,
                        'representante_principal' => [
                            'nacionalidad' => $registro['rep_nacionalidad'] ?? 'V',
                            'cedula_identidad' => $registro['rep_cedula'] ?? '',
                            'nombre_1' => $registro['rep_nombre_1'] ?? '',
                            'apellido_1' => $registro['rep_apellido_1'] ?? '',
                            'parentesco' => $registro['rep_parentesco'] ?? '',
                            'telefono_movil' => $registro['rep_telefono'] ?? '',
                            'profesion' => $registro['rep_profesion'] ?? '',
                            'ingreso_mensual' => $registro['rep_ingreso'] ?? 0,
                            // Ubicación del Representante Principal
                            'estado_nombre' => $registro['estado_nombre'] ?? '',
                            'municipio_nombre' => $registro['municipio_nombre'] ?? '',
                            'parroquia_nombre' => $registro['parroquia_nombre'] ?? '',
                            'sector' => $registro['sector'] ?? '',
                            'calle' => $registro['calle'] ?? '',
                            'tipo_vivienda' => $registro['tipo_vivienda'] ?? '',
                            'numero_vivienda' => $registro['numero_vivienda'] ?? ''
                        ],
                        'representante_secundario' => !empty($registro['rep2_cedula']) ? [
                            'nacionalidad' => $registro['rep2_nacionalidad'] ?? 'V',
                            'cedula_identidad' => $registro['rep2_cedula'] ?? '',
                            'nombre_1' => $registro['rep2_nombre_1'] ?? '',
                            'apellido_1' => $registro['rep2_apellido_1'] ?? '',
                            'parentesco' => $registro['rep2_parentesco'] ?? '',
                            'telefono_movil' => $registro['rep2_telefono'] ?? '',
                            'profesion' => $registro['rep2_profesion'] ?? '',
                            'ingreso_mensual' => $registro['rep2_ingreso'] ?? 0,
                            // Ubicación del Representante Secundario
                            'estado_nombre' => $registro['rep2_estado_nombre'] ?? '',
                            'municipio_nombre' => $registro['rep2_municipio_nombre'] ?? '',
                            'parroquia_nombre' => $registro['rep2_parroquia_nombre'] ?? '',
                            'sector' => $registro['rep2_sector'] ?? '',
                            'calle' => $registro['rep2_calle'] ?? '',
                            'tipo_vivienda' => $registro['rep2_tipo_vivienda'] ?? '',
                            'numero_vivienda' => $registro['rep2_numero_vivienda'] ?? ''
                        ] : null,
                        'salud' => [
                            'reacciones_alergicas' => $registro['reacciones_alergicas'] ?? 'No',
                            'cuales_alergias' => $registro['cuales_alergias'] ?? '',
                            'enfermedades_padecidas' => $registro['enfermedades_padecidas'] ?? '',
                            'atencion_especial' => $registro['atencion_especial'] ?? 'No',
                            'horario_tratamiento' => $registro['horario_tratamiento'] ?? ''
                        ],
                        'antropometrico' => [
                            'estatura' => $registro['estatura'] ?? null,
                            'peso' => $registro['peso'] ?? null,
                            'talla_camisa' => $registro['talla_camisa'] ?? '',
                            'talla_pantalon' => $registro['talla_pantalon'] ?? '',
                            'talla_zapato' => $registro['talla_zapato'] ?? ''
                        ],
                        'extra_curricular' => [
                            'realiza_deportes' => $registro['realiza_deportes'] ?? 'No',
                            'cuales_deportes' => $registro['cuales_deportes'] ?? '',
                            'posee_canaima' => $registro['posee_canaima'] ?? 'No',
                            'serial_canaima' => $registro['serial_canaima'] ?? '',
                            'estado_canaima' => $registro['estado_canaima'] ?? ''
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

    
    public function exportar_reportes($input) {

        $filtros = [
            'periodo_id'        => !empty($input['periodo_id']) ? $input['periodo_id'] : null,
            'grado_id'          => !empty($input['grado_id']) ? $input['grado_id'] : null,
            'seccion_id'        => !empty($input['seccion_id']) ? $input['seccion_id'] : null,
            'tipo_ingreso'      => !empty($input['tipo_ingreso']) ? $input['tipo_ingreso'] : null,
            'estado_id'         => !empty($input['estado_id']) ? $input['estado_id'] : null,
            'municipio_id'      => !empty($input['municipio_id']) ? $input['municipio_id'] : null,
            'parroquia_id'      => !empty($input['parroquia_id']) ? $input['parroquia_id'] : null,
            'atencion_especial' => !empty($input['atencion_especial']) ? $input['atencion_especial'] : null,
            'posee_alergias'    => !empty($input['posee_alergias']) ? $input['posee_alergias'] : null,
            'enfermedad'        => !empty($input['enfermedad']) ? trim($input['enfermedad']) : null,
            'profesion'         => !empty($input['profesion']) ? $input['profesion'] : null,
            'rango_ingreso'     => !empty($input['rango_ingreso']) ? $input['rango_ingreso'] : null,
            'estado_canaima'    => !empty($input['estado_canaima']) ? $input['estado_canaima'] : null,
            'realiza_deportes'  => !empty($input['realiza_deportes']) ? $input['realiza_deportes'] : null,
        ];

        $inscripciones = $this->modelo->filtrar_todas_las_inscripciones($filtros);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_detallado_inscritos_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'Cédula / Escolar Estudiante', 
            'Nombres Estudiante', 
            'Apellidos Estudiante', 
            'Período Académico', 
            'Grado', 
            'Sección', 
            'Estado Estudiante',
            'Cédula Representante',
            'Nombre Representante',
            'Profesión Representante',
            'Ingreso Mensual ($)',
            'Estado (Ubicación)',
            'Municipio',
            'Parroquia'
        ], ';');

        foreach ($inscripciones as $i) {
            $doc_estudiante = $i['cedula_identidad'] ? "{$i['nacionalidad']}-{$i['cedula_identidad']}" : ($i['cedula_escolar'] ?? 'N/P');
            $doc_representante = $i['rep_cedula'] ? "{$i['rep_nacionalidad']}-{$i['rep_cedula']}" : 'N/P';
            
            fputcsv($output, [
                $doc_estudiante,
                ($i['nombre_1'] ?? '') . ' ' . ($i['nombre_2'] ?? ''),
                ($i['apellido_1'] ?? '') . ' ' . ($i['apellido_2'] ?? ''),
                $i['periodo'] ?? 'N/P',
                $i['grado'] ?? 'N/P',
                $i['seccion'] ?? 'N/P',
                $i['estado'] ?? 'Inscrito',
                $doc_representante,
                ($i['rep_nombre'] ?? '') . ' ' . ($i['rep_apellido'] ?? ''),
                $i['profesion'] ?? 'No especificada',
                $i['ingreso_mensual'] ?? '0',
                $i['estado_nombre'] ?? 'N/P',
                $i['municipio_nombre'] ?? 'N/P',
                $i['parroquia_nombre'] ?? 'N/P'
            ], ';');
        }

        fclose($output);
        exit;
    }

}
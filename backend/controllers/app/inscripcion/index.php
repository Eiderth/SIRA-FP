<?php


class Inscripcion_controller {

    private $modelo;

    public function __construct($modelo) {
        $this->modelo = $modelo;
    }

    public function init($action, $input) {

        switch ($action) {
            case 'obtener_geografia':
            $this->obtener_geografia();
            break;

            case 'obtener_parametros_academicos':
            $this->obtener_parametros_academicos();
            break;

            case 'guardar_estudiante': 
            $this->guardar_estudiante($input);
            break;

            case 'obtener_datos_estudiante': 
            $this->obtener_datos_estudiante($input);
            break;

            case 'obtener_datos_representante': 
            $this->obtener_datos_representante($input);
            break;

            default: 
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'acción no válida']);
            break;
        }
    }
    private function obtener_geografia() {
        try {
            http_response_code(200);
            echo json_encode([
                'estado' => 'completado',
                'paises' => $this->modelo->obtener_paises(),
                'estados' => $this->modelo->obtener_estados(),
                'municipios' => $this->modelo->obtener_municipios(),
                'parroquias' => $this->modelo->obtener_parroquias()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Error al obtener los datos geográficos']);
        }
    }

    private function obtener_parametros_academicos() {
        try {
            http_response_code(200);
            echo json_encode([
                'estado' => 'completado',
                'periodos' => $this->modelo->obtener_periodos_academicos('Activo'),
                'secciones' => $this->modelo->obtener_secciones(),
                'grados' => $this->modelo->obtener_grados(),
                'niveles_academicos' => $this->modelo->obtener_niveles_academicos(),
                'grados_secciones' => $this->modelo->obtener_vinculaciones()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Error al obtener los parámetros académicos']);
        }
    }

    private function guardar_estudiante($input) {

            //=== Validaciones ===    

        $input['estudiante']['persona_estudiante']['cedula_escolar'] = $this->modelo->crear_cedula_escolar($input);

        if(strlen($input['estudiante']['persona']['cedula_identidad'] ?? '') < 6) {
            $input['estudiante']['persona']['cedula_identidad'] = null;
        }


        if(strlen($input['representante_secundario']['persona']['cedula_identidad'] ?? '') < 6) {
            $input['representante_secundario']['persona']['cedula_identidad'] = null;
        }

        if(strlen($input['representante_principal']['persona']['cedula_identidad'] ?? '') < 6) {
            echo json_encode([
                'estado' => 'validacion',
                'mensaje' => "La cedula del representante principal no puede tener menos de 6 digitos"
            ]);
            exit();
        }

        if(empty($input['inscripcion']['periodo_id'])) {
            echo json_encode([
                'estado' => 'validacion',
                'mensaje' => "Debe ingresar algun periodo"
            ]);
            exit();
        }

        try {

            if (empty($this->modelo->existe('GRADO_SECCION', 'id, estado', [$input['inscripcion']['grado_seccion_id'], 'Activo'] ))){
                echo json_encode([
                    'estado' => 'validacion',
                    'mensaje' => "la seccion seleccionada no se encuentra activa o no existe"
                ]);
                exit();
            }

            $estudiante_id = null;
            $representante_principal_id = null;
            $representante_secundario_id = null;

            if (!empty($input['estudiante']['persona']['cedula_identidad'])) {
                
                $persona_id = $this->modelo->buscar_valor(
                    'id', 
                    'PERSONA', 
                    'cedula_identidad', 
                    $input['estudiante']['persona']['cedula_identidad'] 
                ) ? : null;
                
                if (!empty($persona_id)) {
                    
                    $estudiante_id = $this->modelo->buscar_valor(
                        'id', 
                        'PERSONA_ESTUDIANTE', 
                        'persona_id', 
                        $persona_id 
                    ) ? : null;
                }
            }


            if (empty($estudiante_id)) {

                $estudiante_id = $this->modelo->buscar_valor(
                    'id', 
                    'PERSONA_ESTUDIANTE', 
                    'cedula_escolar', 
                    $input['estudiante']['persona_estudiante']['cedula_escolar']
                ) ? : null;

            }

            if($estudiante_id) {      
                $existe_registro_periodo = $this->modelo->verificar_inscripcion(
                    $estudiante_id,
                    $input['inscripcion']['periodo_academico_id']
                );
 
                if ($existe_registro_periodo) {
                   
                    echo json_encode([
                      'estado' => 'validacion',
                      'mensaje' => "Es estudiante {$input['estudiante']['persona']['nombre_1']} {$input['estudiante']['persona']['apellido_1']} ya ha sido inscrito en el periodo <br>No se permite doble inscripcion en un mismo periodo"
                    ]);
                
                    exit();
                }
            }

            $this->modelo->iniciar_transaccion();

            $persona_rep_principal_id = $this->modelo->buscar_valor(
                'id',
                'PERSONA',
                'cedula_identidad',
                $input['representante_principal']['persona']['cedula_identidad']
            );

            if ($persona_rep_principal_id) {

                $representante_principal_id = $this->modelo->actualizar_representante(
                    $input['representante_principal'], 
                    $persona_rep_principal_id
                );

            } else {

                $representante_principal_id = $this->modelo->guardar_representante(
                    $input['representante_principal']
                );
            }
            
            if(!empty($input['representante_secundario']['persona']['cedula_identidad'])) {
                
                $persona_rep_secundario_id = $this->modelo->buscar_valor(
                    'id',
                    'PERSONA',
                    'cedula_identidad',
                    $input['representante_secundario']['persona']['cedula_identidad']
                );
                
                if($persona_rep_secundario_id) {
                   
                    $representante_secundario_id = $this->modelo->actualizar_representante(
                        $input['representante_secundario'],
                        $persona_rep_secundario_id
                    );

                } else {

                    $representante_secundario_id = $this->modelo->guardar_representante(
                        $input['representante_secundario'],
                    );
                }
            }

            if (!empty($estudiante_id)) {
                
                $this->modelo->actualizar_estudiante(
                    $input['estudiante'], 
                    $estudiante_id, 
                    $representante_principal_id, 
                    $representante_secundario_id
                );

            } else {
                
                $estudiante_id = $this->modelo->guardar_estudiante(
                    $input['estudiante'], 
                    $representante_principal_id, 
                    $representante_secundario_id
                );
            }

            $llave_inscripcion = $this->modelo->procesar_periodo_inscripcion($input['inscripcion'], $estudiante_id);

            $this->modelo->confirmar_transaccion();

            echo json_encode([
                'estado' => 'completado',
                'cedula_escolar' => $input['estudiante']['persona_estudiante']['cedula_escolar'],
                'llave_inscripcion' => $llave_inscripcion
            ]);

        } catch (PDOException $e) {

            $this->modelo->revertir_transaccion();
            echo json_encode([
                'estado' => 'error',
                'mensaje' => 'Error desconocido',
                'error' => $e->getMessage()
            ]);
        }
    }


    private function obtener_datos_estudiante($input) {
        $historial =[];

        if(!empty($input['cedula_identidad'])) {
            $historial = $this->modelo->obtener_datos_estudiante(
                'cedula_identidad',
                $input['cedula_identidad'],
            );
        } else if (!empty($input['cedula_escolar'])) {
    
            $historial = $this->modelo->obtener_datos_estudiante(
                'cedula_escolar',
                $input['cedula_escolar'],
            );
        }

        if ($historial) {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'se encontro un estudiante',
                'estudiante' => $historial
            ]);
        } else {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'no se encontro ningun estudiante',
            ]);
        }
    }

    private function obtener_datos_representante($input) {
        if(empty($input['cedula_identidad'])) {
            exit();
        }

        $historial = $this->modelo->obtener_datos_representante(
            'cedula_identidad',
            $input['cedula_identidad'],
        );

        if ($historial) {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'se encontro un representante',
                'representante' => $historial
            ]);
        } else {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'no se encontro ningun representante',
            ]);
        }
    }
    
}
<?php

class Inscripcion_controller {

    private $modelo;
    private $sistema;

    public function __construct($modelo, $sistema) {
        $this->modelo = $modelo;
        $this->sistema       = $sistema; 
    }

    public function init($action, $input) {

        switch ($action) {
            case 'traer_parametros':
            $this->traer_parametros();
            break;

            case 'guardar_estudiante': 
            $this->guardar_estudiante($input);
            break;

            case 'obtener_historial_estudiante': 
            $this->obtener_historial_estudiante($input);
            break;

            default: 
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'acción no válida']);
            break;
        }
    }

    private function traer_parametros() {
        $parametros = $this->sistema->obtener_parametros_formulario();
        echo json_encode([
            'estado' => 'completado',
            'parametros' => $parametros
        ]);
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
                'estado' => 'error',
                'mensaje' => "La cedula del representante principal no puede tener menos de 6 digitos"
            ]);
           
            exit();
        }

        try {

            $estudiante_id = null;
            $representante_principal_id = null;
            $representante_secundario_id = null;

            if (isset($input['estudiante']['persona']['cedula_identidad'])) {
                
                $persona_id = $this->modelo->buscar_valor(
                    'id', 
                    'PERSONA', 
                    'cedula_identidad', 
                    $input['estudiante']['persona']['cedula_identidad'] 
                ) ? : null;
                
                if (isset($persona_id)) {
                    
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
                      'estado' => 'error',
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
            
            if(isset($input['representante_secundario']['persona']['cedula_identidad'])) {
                
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

            if (isset($estudiante_id)) {
                
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
                'mensaje' => 'estudiante insertado con exito',
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


    private function obtener_historial_estudiante($input) {
        if(empty($input['cedula_identidad'])) {
            exit();
        }

        $historial = $this->modelo->obtener_historial_estudiante(
            $input['cedula_identidad'],
        );

        if ($historial) {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'se encontro un estudiante',
                'historial' => $historial
            ]);
        } else {
            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'no se encontro ningun estudiante',
                'historial' => null
            ]);
        }
    }
    
}
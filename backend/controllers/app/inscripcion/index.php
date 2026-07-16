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

        if(strlen($input['estudiante']['cedula_escolar'] ?? '') < 9) {
            $input['estudiante']['cedula_escolar'] = null;
        }

        if(strlen($input['estudiante']['cedula_identidad'] ?? '') < 6) {
            $input['estudiante']['cedula_identidad'] = null;
        }


        if(strlen($input['representante_secundario']['cedula'] ?? '') < 6) {
            $input['representante_secundario']['cedula'] = null;
        }

        if(strlen($input['representante_principal']['cedula'] ?? '') < 6) {
            echo json_encode([
                'estado' => 'error',
                'mensaje' => "La cedula del representante principal no puede tener menos de 6 digitos"
            ]);
           
            exit();
        }

        try {

            $cedula_escolar = null;
          
            if($input['estudiante']['cedula_identidad']){
    
                $cedula_escolar = $this->modelo->buscar_valor(
                    'cedula_escolar',
                    'estudiantes',
                    'cedula_identidad',
                    $input['estudiante']['cedula_identidad']
                )?: null;
            }

            if(strlen($cedula_escolar ?? '') != 0 && (
                    $input['estudiante']['cedula_escolar']
                        &&
                    $this->modelo->existe(
                        'estudiantes',
                        'cedula_escolar',
                        $input['estudiante']['cedula_escolar']
                    )
                )
            ){
                $cedula_escolar = $input['estudiante']['cedula_escolar'];
                
            }

            if (strlen($cedula_escolar ?? '') < 6) {
                $cedula_escolar = $this->modelo->crear_cedula_escolar($input);
            }

            $existe_registro_periodo = $this->modelo->verificar_inscripcion(
                $input['estudiante']['cedula_identidad'],
                $cedula_escolar,
                $input['periodo']['id']
            );

            // echo json_encode([
            //     'estado' => 'error', 
            //     'existe' => $existe_registro_periodo, 
            //     'periodo_id' => $input['periodo']['id'],
            //     'cedula_escolar' => $cedula_escolar
            // ]);
            // exit();

            if ($existe_registro_periodo) {
               
                echo json_encode([
                  'estado' => 'error',
                  'mensaje' => "Es estudiante {$input['estudiante']['nombre_1']} {$input['estudiante']['apellido_1']} ya ha sido inscrito en el periodo <br>No se permite doble inscripcion en un mismo periodo"
                ]);
                
                exit();
            }

            $this->modelo->iniciar_transaccion();

            $existe_r_principal = $this->modelo->existe(
                'representantes',
                'cedula',
                $input['representante_principal']['cedula']
            );

            if ($existe_r_principal) {

                $this->modelo->actualizar_representante(
                    $input['representante_principal'],
                    $input['direccion_r_principal']
                );

            } else {

                $this->modelo->guardar_representante(
                    $input['representante_principal'],
                    $input['direccion_r_principal']
                );
            }


            if($input['representante_secundario']['cedula']){
               
                $existe_r_secundario = $this->modelo->existe(
                    'representantes',
                    'cedula',
                    $input['representante_secundario']['cedula']
                );
                
                if($existe_r_secundario) {
                   
                    $this->modelo->actualizar_representante(
                        $input['representante_secundario'],
                        $input['direccion_r_secundario']
                    );

                } else {

                    $this->modelo->guardar_representante(
                        $input['representante_secundario'],
                        $input['direccion_r_secundario']
                    );
                }
            }


            $input['estudiante']['representante_principal_cedula'] = $input['representante_principal']['cedula'];

            $input['estudiante']['representante_secundario_cedula'] = $input['representante_secundario']['cedula'];

            $existe_estudiante = (
                $input['estudiante']['cedula_identidad'] !== null
                &&
                $this->modelo->existe(
                    'estudiantes',
                    'cedula_identidad',
                    $input['estudiante']['cedula_identidad']
                )
            ) || $this->modelo->existe('estudiantes', 'cedula_escolar', $cedula_escolar);


            if($existe_estudiante){

                $input['estudiante']['cedula_escolar'] = $cedula_escolar;

                $this->modelo->actualizar_estudiante($input);

            } else {

                $input['estudiante']['cedula_escolar'] = $cedula_escolar;

                $this->modelo->guardar_estudiante($input);

            }

            $llave_inscripcion = $this->modelo->procesar_periodo_inscripcion($input, $cedula_escolar);
            $this->modelo->confirmar_transaccion();

            echo json_encode([
                'estado' => 'completado',
                'mensaje' => 'estudiante insertado con exito',
                'cedula_escolar' => $cedula_escolar,
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
        if(empty($input['cedula_identidad']) && empty($input['cedula_escolar'])) {
            exit();
        }

        $historial = $this->modelo->obtener_historial_estudiante(
            $input['cedula_identidad'],
            $input['cedula_escolar']
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
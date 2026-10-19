<?php 

class academicos_controller
{
    private $modelo;
    
    function __construct($modelo ){
        $this->modelo = $modelo;
    }

    public function init($action, $input) {
        header('Content-Type: application/json');

        switch ($action) {
            case 'obtener_periodos_academicos': 
                $this->obtener_periodos_academicos();
                break;    
            case 'crear_periodo_academico':
                $this->crear_periodo_academico($input);
                break;
            case 'eliminar_periodo_academico': 
                $this->eliminar_periodo_academico($input);
                break;
            case 'alternar_estado_periodo_academico': 
                $this->alternar_estado_periodo_academico($input);
                break;

            case 'obtener_niveles_academicos': 
                $this->obtener_niveles_academicos();
                break;
            case 'crear_nivel_academico':
                $this->crear_nivel_academico($input);
                break;
            case 'eliminar_nivel_academico': 
                $this->eliminar_nivel_academico($input);
                break;

            case 'obtener_grados_niveles_academicos': 
                $this->obtener_grados_niveles_academicos();
                break;    
            case 'crear_grado':
                $this->crear_grado($input);
                break;
            case 'eliminar_grado': 
                $this->eliminar_grado($input);
                break;

            case 'obtener_secciones': 
                $this->obtener_secciones();
                break;    
            case 'crear_seccion':
                $this->crear_seccion($input);
                break;
            case 'eliminar_seccion': 
                $this->eliminar_seccion($input);
                break;

            case 'obtener_vinculaciones':
                $this->obtener_vinculaciones();
                break;
            case 'crear_vinculacion':
                $this->crear_vinculacion($input);
                break;
            case 'eliminar_vinculacion': 
                $this->eliminar_vinculacion($input);
                break;
            case 'alternar_estado_vinculacion': 
                $this->alternar_estado_vinculacion($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(['estado' => 'error', 'error' => 'La acción solicitada no fue encontrada']);
                break;
        }
    } 

    // ==========================================
    // === PERIODOS ACADÉMICOS
    // ==========================================

    private function obtener_periodos_academicos() {

        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            'periodos_academicos' => $this->modelo->obtener_periodos_academicos()
        ]);

    }

    private function crear_periodo_academico($input) {
        
        if (empty($input['nombre'])) {
        
            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, ingrese el nombre del período académico']);
            return;
        
        } else if ($this->modelo->existe('PERIODO_ACADEMICO', 'nombre', $input['nombre'])) {
        
            http_response_code(409); 
            echo json_encode(['estado' => 'error', 'error' => 'Ya existe un período académico con ese nombre']);
            return;
        
        } 

        try {

            $periodo_id = $this->modelo->crear_periodo_academico($input['nombre']);
            
            if (!empty($periodo_id)) {
            
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'creado' => true]);
            
            } else {

                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo crear el período académico']);

            }

        } catch (PDOException $e) {

            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);

        }
    
    }

    private function eliminar_periodo_academico($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID del período académico no es válido']);
            return;

        } else if ($this->modelo->existe('INSCRIPCION', 'periodo_academico_id', $input['id'])) {
        
            http_response_code(403); 
            echo json_encode(['estado' => 'error', 'error' => 'No se puede eliminar el período porque tiene inscripciones asociadas']);
            return;
        
        } 

        try {

            $eliminado = $this->modelo->eliminar_periodo_academico($input['id']);
            
            if (!empty($eliminado)) {
            
                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'eliminado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar el período académico']);
            
            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        } 

    }

    private function alternar_estado_periodo_academico($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, indique el período académico']);
            return;

        }

        try {

            $estado_actual = $this->modelo->buscar_valor('estado', 'PERIODO_ACADEMICO', 'id', $input['id']);

            if ($estado_actual === 'Cerrado') {
                if($this->modelo->existe('PERIODO_ACADEMICO', 'estado', 'Activo')){
                    echo json_encode(['estado' => 'error', 'error' => 'No puede haber más de un periodo activo']);
                    return;
                }
            }

            $alternado = $this->modelo->alternar_estado_periodo_academico($input['id']);
            
            if (!empty($alternado)) {

                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'alternado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo cambiar el estado del período académico']);        

            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);

        }
    
    }

    // ==========================================
    // === NIVELES ACADÉMICOS
    // ==========================================

    private function obtener_niveles_academicos() {
     
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            'niveles_academicos' => $this->modelo->obtener_niveles_academicos()
        ]);

    }

    private function crear_nivel_academico($input) {
        
        if (empty($input['nombre'])) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, ingrese el nombre del nivel académico']);
            return;

        } else if ($this->modelo->existe('NIVEL_ACADEMICO', 'nombre', $input['nombre'])) {
          
            http_response_code(409);
            echo json_encode(['estado' => 'error', 'error' => 'Ya existe un nivel académico con ese nombre']);
            return;
        
        } 

        try {

            $nivel_id = $this->modelo->crear_nivel_academico($input['nombre']);

            if (!empty($nivel_id)) {
            
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'creado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo crear el nivel académico']);
            
            }
        
        } catch (PDOException $e) {
        
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    private function eliminar_nivel_academico($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID del nivel académico no es válido']);
            return;

        } else if ($this->modelo->existe('GRADO', 'nivel_academico_id', $input['id'])) {
            
            http_response_code(403);
            echo json_encode(['estado' => 'error', 'error' => 'No se puede eliminar el nivel porque tiene grados asociados']);
            return;

        } 

        try {
            
            $eliminado = $this->modelo->eliminar_nivel_academico($input['id']);
            
            if (!empty($eliminado)) {
            
                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'eliminado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar el nivel académico']);
            
            }
        
        } catch (PDOException $e) {
        
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    // ==========================================
    // === GRADOS
    // ==========================================

    private function obtener_grados_niveles_academicos() {
        
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            'grados' => $this->modelo->obtener_grados(),
            'niveles_academicos' => $this->modelo->obtener_niveles_academicos()
        ]);

    }

    private function crear_grado($input) {
        
        if (empty($input['nombre']) || empty($input['nivel_academico_id'])) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, complete todos los campos obligatorios']);
            return;

        } else if ($this->modelo->existe('GRADO', 'nombre', $input['nombre'])) {

            http_response_code(409);
            echo json_encode(['estado' => 'error', 'error' => 'Ya existe un grado con ese nombre']); 
            return;     
        
        } 

        try {
        
            $grado_id = $this->modelo->crear_grado($input['nombre'], $input['nivel_academico_id']);
        
            if (!empty($grado_id)) {
        
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'creado' => true]);
        
            } else {

                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo crear el grado']); 
            
            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);

        }

    }

    private function eliminar_grado($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID del grado no es válido']);
            return;

        } else if ($this->modelo->existe('GRADO_SECCION', 'grado_id', $input['id'])) {
        
            http_response_code(403);
            echo json_encode(['estado' => 'error', 'error' => 'No se puede eliminar el grado porque está vinculado a secciones']);
            return;

        } 

        try {

            $eliminado = $this->modelo->eliminar_grado($input['id']);
            
            if (!empty($eliminado)) {
            
                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'eliminado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar el grado']);        
            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    // ==========================================
    // === SECCIONES
    // ==========================================

    private function obtener_secciones() {
    
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            'secciones' => $this->modelo->obtener_secciones()
        ]);
    
    }

    private function crear_seccion($input) {
        
        if (empty($input['nombre'])) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, ingrese el nombre de la sección']);
            return;

        } else if ($this->modelo->existe('SECCION', 'nombre', $input['nombre'])) {
            
            http_response_code(409);
            echo json_encode(['estado' => 'error', 'error' => 'Ya existe una sección con ese nombre']); 
            return;     
        
        } 

        try {
        
            $seccion_id = $this->modelo->crear_seccion($input['nombre']);
        
            if (!empty($seccion_id)) {
        
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'creada' => true]);

            } else {

                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo crear la sección']); 
            
            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
   
    }

    private function eliminar_seccion($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID de la sección no es válido']);
            return;

        } else if ($this->modelo->existe('GRADO_SECCION', 'seccion_id', $input['id'])) {
            
            http_response_code(403);
            echo json_encode(['estado' => 'error', 'error' => 'No se puede eliminar la sección porque está vinculada a un grado']);
            return; 
        
        } 

        try {
        
            $eliminado = $this->modelo->eliminar_seccion($input['id']);
        
            if (!empty($eliminado)) {

                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'eliminada' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar la sección']);        
            
            }
        
        } catch (PDOException $e) {
        
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    // ==========================================
    // === VINCULAR GRADOS-SECCIONES
    // ==========================================

    private function obtener_vinculaciones() {
    
        http_response_code(200);
        echo json_encode([
            'estado' => 'completado', 
            'vinculaciones' => $this->modelo->obtener_vinculaciones(),
            'niveles_academicos' => $this->modelo->obtener_niveles_academicos(),
            'grados' => $this->modelo->obtener_grados(),
            'secciones' => $this->modelo->obtener_secciones()
        ]);
    
    }

    private function crear_vinculacion($input) {
        
        if (
            empty($input['grado_id']) || 
            empty($input['seccion_id']) || 
            (int) $input['grado_id'] < 1 ||
            (int) $input['seccion_id'] < 1 
        ) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'Por favor, seleccione un grado y una sección válidos']);
            return;

        } else if ($this->modelo->existe('GRADO_SECCION', 'grado_id, seccion_id', [$input['grado_id'], $input['seccion_id']])) {
            
            http_response_code(409);
            echo json_encode(['estado' => 'error', 'error' => 'Esta asignación de grado y sección ya existe']); 
            return;     
        
        } 

        try {
        
            $grado_seccion_id = $this->modelo->crear_vinculacion(
                $input['grado_id'],
                $input['seccion_id']
            );
        
            if (!empty($grado_seccion_id)) {
        
                http_response_code(201);
                echo json_encode(['estado' => 'completado', 'creado' => true]);
        
            } else {
        
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo realizar la asignación']); 
        
            }
        
        } catch (PDOException $e) {
        
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    private function eliminar_vinculacion($input) {

        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID de la asignación no es válido']);
            return;

        } else if ($this->modelo->existe('INSCRIPCION', 'grado_seccion_id', $input['id'])) {
            
            http_response_code(403);
            echo json_encode(['estado' => 'error', 'error' => 'No se puede eliminar la asignación porque tiene estudiantes inscritos']);
            return; 
        
        } 

        try {
            
            $eliminado = $this->modelo->eliminar_vinculacion($input['id']);
        
            if (!empty($eliminado)) {
        
                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'eliminado' => true]);
        
            } else {
        
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo eliminar la asignación']);        
            }
        
        } catch (PDOException $e) {
        
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

    private function alternar_estado_vinculacion($input) {
        
        if (empty($input['id']) || (int) $input['id'] < 1) {

            http_response_code(400); 
            echo json_encode(['estado' => 'error', 'error' => 'El ID de la asignación no es válido']);
            return;

        }

        try {
    
            $alternado = $this->modelo->alternar_estado_vinculacion($input['id']);
            
            if (!empty($alternado)) {
            
                http_response_code(200);
                echo json_encode(['estado' => 'completado', 'alternado' => true]);
            
            } else {
            
                http_response_code(500);
                echo json_encode(['estado' => 'error', 'error' => 'No se pudo cambiar el estado de la asignación']);        
            }

        } catch (PDOException $e) {
            
            http_response_code(500);
            echo json_encode(['estado' => 'error', 'error' => 'Ocurrió un error en la base de datos']);
        
        }
    
    }

}
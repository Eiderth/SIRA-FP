<?php 
require_once __DIR__. '/utils.php';

trait Estudiante  {
	use Utils;

	//=== Metodos para el manejo de datos de estudiantes ===

	public function guardar_estudiante ($data, $rep_principal_id, $rep_secundario_id) {

		$llave_antropometricos = $this->insertar('ANTROPOMETRICO', $data['antropometrico']);

		$llave_salud = $this->insertar('SALUD', $data['salud']);

		$llave_extra_curriculares = $this->insertar('EXTRA_CURRICULAR', $data['extra_curricular']);

		$llave_persona = $this->insertar('PERSONA', $data['persona']);

		return $this->insertar('PERSONA_ESTUDIANTE', [
			...$data['persona_estudiante'],
			'persona_id' => $llave_persona,
			'antropometrico_id' => $llave_antropometricos,
			'salud_id' => $llave_salud,
			'extra_curricular_id' => $llave_extra_curriculares,
			'representante_principal_id' => $rep_principal_id,
			'representante_secundario_id' => $rep_secundario_id
		]);
	}

	public function actualizar_estudiante($data, $id, $rep_principal_id, $rep_secundario_id) {

		if(!empty($data['persona']['cedula_identidad'])){ //actualizar unicamente la cedula si no hay

			$persona_id = $this->buscar_valor('persona_id', 'PERSONA_ESTUDIANTE', 'id', $id);
			$cedula_identidad = $this->buscar_valor('cedula_identidad', 'PERSONA', 'id', $persona_id);
			
			if (empty($cedula_identidad)) {
				$this->actualizar(
					'PERSONA', 
					['cedula_identidad' => $data['persona']['cedula_identidad']], 
					'id', 
					$persona_id
				);
			}

		}

		$antropometrico_id = $this->buscar_valor('antropometrico_id', 'PERSONA_ESTUDIANTE', 'id', $id);
		$this->actualizar('ANTROPOMETRICO', $data['antropometrico'], 'id' , $antropometrico_id);

		$salud_id = $this->buscar_valor('salud_id', 'PERSONA_ESTUDIANTE', 'id', $id);
		$this->actualizar('SALUD', $data['salud'], 'id' , $salud_id);

		$extra_curricular_id = $this->buscar_valor('extra_curricular_id', 'PERSONA_ESTUDIANTE', 'id', $id);
		$this->actualizar('EXTRA_CURRICULAR', $data['extra_curricular'], 'id' , $extra_curricular_id);

		$this->actualizar(
			'PERSONA_ESTUDIANTE',
			['representante_principal_id' => $rep_principal_id],
			'id', 
			$id
		);
		
		if (!empty($rep_secundario_id)) {
			$this->actualizar('PERSONA_ESTUDIANTE', ['representante_secundario_id' => $rep_secundario_id], 'id', $id);
		} 

	}


	public function obtener_datos_estudiante($key, $value) {
        $estudiante = null;
        
        if ($key === 'cedula_identidad'){
        
        	$id = $this->buscar_valor('id', 'PERSONA', 'cedula_identidad', $value);
        	$estudiante_id = $this->buscar_valor('id', 'PERSONA_ESTUDIANTE', 'persona_id', $id);
        	$estudiante =  $this->buscar_todo('PERSONA_ESTUDIANTE', 'id', $estudiante_id);
        
        } else if ($key === 'id') {
	    
	        $estudiante = $this->buscar_todo('PERSONA_ESTUDIANTE', 'id', $value);
		
		}
	
        $resultado = [];

        $resultado['estudiante'] = [
            'persona_estudiante' => $estudiante,
            'persona' =>  $this->buscar_todo('PERSONA', 'id', $estudiante['persona_id']),
            'antropometricos' => $this->buscar_todo('ANTROPOMETRICO', 'id', $estudiante['antropometrico_id']),
            'salud' =>  $this->buscar_todo('SALUD', 'id', $estudiante['salud_id'] ),
            'extra_curriculares' =>  $this->buscar_todo('EXTRA_CURRICULAR', 'id', $estudiante['extra_curricular_id'])
        ];

        $representante_principal = $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_principal_id']);
        $resultado['representante_principal'] = [
            'persona_representante' => $representante_principal,
            'persona' =>    $this->buscar_todo('PERSONA', 'id', $representante_principal['persona_id']),
        ];

        if (!empty($estudiante['representante_secundario_id'])) {
            $representante_secundario = $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_secundario_id']);
            $resultado['representante_secundario'] = [
                'persona_representante' => $representante_secundario,
                'persona' => $this->buscar_todo('PERSONA', 'id', $representante_secundario['persona_id'])
            ];
        }

        return $resultado;

    } 

 	public function buscar_coincidencias_estudiante($busqueda, $pagina = 1, $porPagina = 10) {
	    $offset = ($pagina - 1) * $porPagina;
	    $parametros = [];

	    $sql = "
	        SELECT 
	            pe.id AS estudiante_id,
	            p_est.nacionalidad AS est_nacionalidad,
	            p_est.cedula_identidad AS est_cedula_identidad,
	            pe.cedula_escolar AS est_cedula_escolar,
	            p_est.nombre_1 AS est_nombre_1,
	            p_est.apellido_1 AS est_apellido_1,
	            p_est.nombre_2 AS est_nombre_2,
	            p_est.apellido_2 AS est_apellido_2,
	            pe.estado AS est_estado,
	            
	            pr.id AS representante_id,
	            p_rep.cedula_identidad AS rep_cedula_identidad,
	            p_rep.nacionalidad AS rep_nacionalidad,
	            p_rep.nombre_1 AS rep_nombre_1,
	            p_rep.apellido_1 AS rep_apellido_1,
	            p_rep.nombre_2 AS rep_nombre_2,
	            p_rep.apellido_2 AS rep_apellido_2,
	            pr.telefono_movil AS rep_telefono_movil
	            
	        FROM PERSONA p_est
	        INNER JOIN PERSONA_ESTUDIANTE pe ON p_est.id = pe.persona_id
	        INNER JOIN PERSONA_REPRESENTANTE pr ON pe.representante_principal_id = pr.id
	        INNER JOIN PERSONA p_rep ON pr.persona_id = p_rep.id
	        LEFT JOIN INSCRIPCION ins ON pe.id = ins.estudiante_id
	        LEFT JOIN GRADO_SECCION gs ON ins.grado_seccion_id = gs.id
	        WHERE 1=1
	    ";

	    $condiciones = [];

	    if (!empty($busqueda['periodo_academico_id'])) {
	        $condiciones[] = "ins.periodo_academico_id = :periodo_academico_id";
	        $parametros[':periodo_academico_id'] = $busqueda['periodo_academico_id'];
	    }

	    if (!empty($busqueda['grado_id'])) {
	        $condiciones[] = "gs.grado_id = :grado_id";
	        $parametros[':grado_id'] = $busqueda['grado_id'];
	    }

	    if (!empty($busqueda['seccion_id'])) {
	        $condiciones[] = "gs.seccion_id = :seccion_id";
	        $parametros[':seccion_id'] = $busqueda['seccion_id'];
	    }

	    if (!empty($busqueda['estado'])) {
	        $condiciones[] = "pe.estado = :estado";
	        $parametros[':estado'] = $busqueda['estado'];
	    }

	    if (!empty($busqueda['busqueda_general'])) {
	        $palabras = array_filter(explode(' ', trim($busqueda['busqueda_general'])));
	        $condiciones_palabras = [];

	        foreach ($palabras as $index => $palabra) {
	            if (empty($palabra)) continue;
	            $key = ":busqueda_general_{$index}";
	            
	            $condiciones_palabras[] = "(
	                p_est.cedula_identidad LIKE {$key} 
	                OR p_est.nombre_1 LIKE {$key} 
	                OR p_est.nombre_2 LIKE {$key}
	                OR p_est.apellido_1 LIKE {$key} 
	                OR p_est.apellido_2 LIKE {$key}
	            )";

	            $parametros[$key] = "%" . $palabra . "%";
	        }

	        if (!empty($condiciones_palabras)) {
	            $condiciones[] = "(" . implode(" AND ", $condiciones_palabras) . ")";
	        }
	    }

	    if (count($condiciones) > 0) {
	        $sql .= " AND " . implode(" AND ", $condiciones);
	    }

	    $sql .= " GROUP BY pe.id ORDER BY p_est.nombre_1 ASC, p_est.nombre_2 ASC, p_est.apellido_1 ASC, p_est.apellido_2 ASC LIMIT :limite OFFSET :offset";

	    $stmt = $this->db->prepare($sql);

	    foreach ($parametros as $key => $val) {
	        $stmt->bindValue($key, $val);
	    }
	    
	    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
	    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

	    $stmt->execute();
	    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

    public function contar_coincidencias_estudiante($busqueda) {
	    $sql = "
	        SELECT COUNT(DISTINCT pe.id) AS total
	        FROM PERSONA p_est
	        INNER JOIN PERSONA_ESTUDIANTE pe ON p_est.id = pe.persona_id
	        INNER JOIN PERSONA_REPRESENTANTE pr ON pe.representante_principal_id = pr.id
	        INNER JOIN PERSONA p_rep ON pr.persona_id = p_rep.id
	        LEFT JOIN INSCRIPCION ins ON pe.id = ins.estudiante_id
	        LEFT JOIN GRADO_SECCION gs ON ins.grado_seccion_id = gs.id
	        WHERE 1=1
	    ";

	    $condiciones = [];
	    $parametros = [];

	    if (!empty($busqueda['periodo_academico_id'])) {
	        $condiciones[] = "ins.periodo_academico_id = :periodo_academico_id";
	        $parametros[':periodo_academico_id'] = $busqueda['periodo_academico_id'];
	    }

	    if (!empty($busqueda['grado_id'])) {
	        $condiciones[] = "gs.grado_id = :grado_id";
	        $parametros[':grado_id'] = $busqueda['grado_id'];
	    }

	    if (!empty($busqueda['seccion_id'])) {
	        $condiciones[] = "gs.seccion_id = :seccion_id";
	        $parametros[':seccion_id'] = $busqueda['seccion_id'];
	    }

	    if (!empty($busqueda['estado'])) {
	        $condiciones[] = "pe.estado = :estado";
	        $parametros[':estado'] = $busqueda['estado'];
	    }

	    if (!empty($busqueda['busqueda_general'])) {
	        $palabras = array_filter(explode(' ', trim($busqueda['busqueda_general'])));
	        $condiciones_palabras = [];

	        foreach ($palabras as $index => $palabra) {
	            if (empty($palabra)) continue;
	            $key = ":busqueda_general_{$index}";
	            
	            $condiciones_palabras[] = "(
	                p_est.cedula_identidad LIKE {$key} 
	                OR p_est.nombre_1 LIKE {$key} 
	                OR p_est.nombre_2 LIKE {$key}
	                OR p_est.apellido_1 LIKE {$key} 
	                OR p_est.apellido_2 LIKE {$key}
	            )";

	            $parametros[$key] = "%" . $palabra . "%";
	        }

	        if (!empty($condiciones_palabras)) {
	            $condiciones[] = "(" . implode(" AND ", $condiciones_palabras) . ")";
	        }
	    }

	    if (count($condiciones) > 0) {
	        $sql .= " AND " . implode(" AND ", $condiciones);
	    }

	    $stmt = $this->db->prepare($sql);
	    $stmt->execute($parametros);
	    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

	    return (int)$resultado['total'];
	}



}

?>

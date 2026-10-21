<?php 
require_once __DIR__. '/utils.php';

trait Estudiante  {
	use Utils;


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

        $persona = [];
        $persona_estudiante = [];

    	// var_dump('estoy aqui', $value);
        if ($key === 'cedula_identidad') {
        	$persona = $this->buscar_todo('PERSONA', 'cedula_identidad', $value);
        	if ($persona['id']) $persona_estudiante = $this->buscar_todo('PERSONA_ESTUDIANTE', 'persona_id', $persona['id']);
        } 
        
        if ($key === 'cedula_escolar') { //alfo anda mal aqui
	        $persona_estudiante = $this->buscar_todo('PERSONA_ESTUDIANTE','cedula_escolar', $value);
        	if ($persona_estudiante['persona_id']) $persona = $this->buscar_todo('PERSONA', 'id', $persona_estudiante['persona_id']);
        } 

        if ($key === 'id') {
	        $persona_estudiante = $this->buscar_todo('PERSONA_ESTUDIANTE', 'id', $value);
        	if ($persona_estudiante['persona_id']) $persona = $this->buscar_todo('PERSONA', 'id', $persona_estudiante['persona_id']);
        }

		if (empty($persona) || empty($persona_estudiante)) return;

		$antropometrico = $this->buscar_todo('ANTROPOMETRICO', 'id', $persona_estudiante['antropometrico_id']);
		$salud = $this->buscar_todo('SALUD', 'id', $persona_estudiante['salud_id']);
		$extra_curricular = $this->buscar_todo('EXTRA_CURRICULAR', 'id', $persona_estudiante['extra_curricular_id']);

		if (!empty($persona_estudiante['parroquia_nacimiento_id'])) {
    
		    $sql = "SELECT 
		                mun.estado_id AS estado_nacimiento_id, 
		                mun.id AS municipio_nacimiento_id 
		            FROM PARROQUIA par
		            JOIN MUNICIPIO mun ON par.municipio_id = mun.id
		            WHERE par.id = :parroquia_id";

		    $stmt = $this->db->prepare($sql);
		    $stmt->execute([':parroquia_id' => $persona_estudiante['parroquia_nacimiento_id']]);
		    $geo = $stmt->fetch(PDO::FETCH_ASSOC);

		    if ($geo) {
		        $persona_estudiante['estado_nacimiento_id'] = $geo['estado_nacimiento_id'];
		        $persona_estudiante['municipio_nacimiento_id'] = $geo['municipio_nacimiento_id'];
		    }
		}

        return [
            'persona_estudiante' => $persona_estudiante,
            'persona' => $persona,
            'antropometrico' => $antropometrico,
            'salud' => $salud,
            'extra_curricular' => $extra_curricular,
            'representante_principal_id' => $persona_estudiante['representante_principal_id'],
            'representante_secundario_id' => $persona_estudiante['representante_secundario_id']
        ];
    }
    
    public function obtener_resumen_salud($periodo_activo_id) {
        $sql = "SELECT 
                    SUM(CASE WHEN s.atencion_especial = 'Si' THEN 1 ELSE 0 END) AS atencion_especial,
                    SUM(CASE WHEN s.reacciones_alergicas = 'Si' THEN 1 ELSE 0 END) AS alergias,
                    SUM(CASE WHEN s.atendido_por_especialista = 'Si' THEN 1 ELSE 0 END) AS bajo_especialista
                  FROM INSCRIPCION i
                  INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                  INNER JOIN SALUD s ON pe.salud_id = s.id
                  WHERE i.periodo_academico_id = :periodo_id AND pe.estado = 'Activo'";
                  
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: ['atencion_especial' => 0, 'alergias' => 0, 'bajo_especialista' => 0];
    }

    public function obtener_promedios_antropometricos($periodo_activo_id) {
        $sql = "SELECT 
                    ant.talla_camisa,
                    ant.talla_pantalon,
                    ant.talla_zapato
                  FROM INSCRIPCION i
                  INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                  INNER JOIN ANTROPOMETRICO ant ON pe.antropometrico_id = ant.id
                  WHERE i.periodo_academico_id = :periodo_id AND pe.estado = 'Activo'";
                  
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'talla_camisa' => $this->calcular_moda($todos, 'talla_camisa'),
            'talla_pantalon' => $this->calcular_moda($todos, 'talla_pantalon'),
            'talla_zapato' => $this->calcular_moda($todos, 'talla_zapato')
        ];
    }

    public function obtener_resumen_deportes($periodo_activo_id) {
        $sql = "SELECT 
                    COUNT(*) AS total_estudiantes,
                    SUM(CASE WHEN e.realiza_deportes = 'Si' THEN 1 ELSE 0 END) AS hacen_deporte
                 FROM INSCRIPCION i
                 INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                 INNER JOIN EXTRA_CURRICULAR e ON pe.extra_curricular_id = e.id
                 WHERE i.periodo_academico_id = :periodo_id AND pe.estado = 'Activo'";
                 
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_est = (int)($res['total_estudiantes'] ?? 0);
        $hacen_dep = (int)($res['hacen_deporte'] ?? 0);
        $porcentaje = $total_est > 0 ? round(($hacen_dep / $total_est) * 100) : 0;

        return ['porcentaje_participacion' => $porcentaje];
    }

    public function obtener_resumen_canaimas($periodo_activo_id) {
        $sql = "SELECT 
                    SUM(CASE WHEN e.estado_canaima = 'Operativa' THEN 1 ELSE 0 END) AS operativas
                 FROM INSCRIPCION i
                 INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                 INNER JOIN EXTRA_CURRICULAR e ON pe.extra_curricular_id = e.id
                 WHERE i.periodo_academico_id = :periodo_id AND pe.estado = 'Activo'";
                 
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return ['operativas' => $res['operativas'] ?? 0];
    }

    private function calcular_moda($array, $columna) {
        if (empty($array)) return '--';
        $conteos = [];
        foreach ($array as $row) {
            $val = trim($row[$columna] ?? '');
            if ($val !== '') {
                $conteos[$val] = ($conteos[$val] ?? 0) + 1;
            }
        }
        if (empty($conteos)) return '--';
        arsort($conteos);
        return array_key_first($conteos);
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

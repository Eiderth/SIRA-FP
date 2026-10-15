<?php
require_once __DIR__. '/utils.php';

trait Inscripcion {
	use Utils;

    //=== Metodos para el manejo de inscripciones ===

	public function crear_cedula_escolar($data) {
		return "{$data['estudiante']['persona']['nacionalidad']}{$data['estudiante']['persona']['fecha_nacimiento'][2]}{$data['estudiante']['persona']['fecha_nacimiento'][3]}{$data['estudiante']['persona_estudiante']['numero_hijo']}{$data['representante_principal']['persona']['cedula_identidad']}";
	}

	public function procesar_periodo_inscripcion($data_inscripcion, $estudiante_id) {

		return $this->insertar('INSCRIPCION', [
			...$data_inscripcion,
			'estudiante_id' => $estudiante_id,
		]);

	}

	public function verificar_inscripcion ($estudiante_id, $periodo_id) {

		$stmt = $this->db->prepare("
			SELECT 1 FROM INSCRIPCION i
			JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
			JOIN PERIODO_ACADEMICO p ON i.periodo_academico_id = p.id
			WHERE e.id = :estudiante_id AND p.id = :periodo_id LIMIT 1
		");

		$stmt->execute([
			'estudiante_id' => $estudiante_id,
			'periodo_id' => $periodo_id
		]);

		return (int) $stmt->fetchColumn() === 1;
	}

	public function buscar_planilla_inscripcion($id){
		$stmt = $this->db->prepare("
	      SELECT i.*, p.*, e.*, g.nombre AS grado, s.nombre AS seccion, pa.nombre AS periodo, na.nombre AS nivel_academico
	      FROM INSCRIPCION i
	      INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
	      INNER JOIN PERSONA p ON e.persona_id = p.id
	      INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
	      INNER JOIN GRADO g ON gs.grado_id = g.id
	      INNER JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
	      INNER JOIN SECCION s ON gs.seccion_id = s.id
	      INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
	      WHERE i.id = :id LIMIT 1
	    ");
	    $stmt->execute(['id' => $id]);
	    return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function listar_inscripciones($limit = '') {

		$consulta =  "SELECT 
		  i.id AS id_inscripcion,
          i.fecha_inscripcion,
          pe.nombre_1, pe.apellido_1, pe.nacionalidad, 
          pe.cedula_identidad, e.cedula_escolar, e.estado,
          s.nombre AS seccion, g.nombre AS grado, pa.nombre as periodo
          FROM INSCRIPCION i
          INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
          INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
          INNER JOIN GRADO g ON gs.grado_id = g.id
          INNER JOIN SECCION s ON gs.seccion_id = s.id
          INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
          INNER JOIN PERSONA pe ON e.persona_id = pe.id

          ORDER BY i.fecha_inscripcion DESC";

        if (!empty($limit)) {
         	$consulta = $consulta . " LIMIT {$limit}";
        }

		$stmt = $this->db->query($consulta);
        
		return $stmt->fetchAll(PDO::FETCH_ASSOC);	

	}

	public function filtrar_inscripciones($periodo_id = null, $grado_id = null, $seccion_id = null, $pagina = 1, $limite = 15) {

	    $pagina = max(1, (int)$pagina);
	    $limite = max(1, (int)$limite);

	    $offset = ($pagina - 1) * $limite;

	    $sql = "SELECT 
	                i.id AS id_inscripcion,
	                pe.nombre_1, pe.apellido_1, pe.nacionalidad, 
	                pe.cedula_identidad, e.cedula_escolar, e.estado,
	                s.nombre AS seccion, g.nombre AS grado, pa.nombre AS periodo
	            FROM INSCRIPCION i
	            INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
	            INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
	            INNER JOIN GRADO g ON gs.grado_id = g.id
	            INNER JOIN SECCION s ON gs.seccion_id = s.id
	            INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
	            INNER JOIN PERSONA pe ON e.persona_id = pe.id";

	    $condiciones = [];
	    $params = [];

	    if (!empty($periodo_id)) {
	        $condiciones[] = "i.periodo_academico_id = :periodo_id";
	        $params['periodo_id'] = $periodo_id;
	    }

	    if (!empty($grado_id)) {
	        $condiciones[] = "gs.grado_id = :grado_id";
	        $params['grado_id'] = $grado_id;
	    }

	    if (!empty($seccion_id)) {
	        $condiciones[] = "gs.seccion_id = :seccion_id";
	        $params['seccion_id'] = $seccion_id;
	    }

	    if (count($condiciones) > 0) {
	        $sql .= " WHERE " . implode(" AND ", $condiciones);
	    }

	    $sql .= " ORDER BY i.fecha_inscripcion DESC LIMIT {$limite} OFFSET {$offset}";

	    $stmt = $this->db->prepare($sql);
	    $stmt->execute($params);

	    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	public function contar_total_inscripciones($periodo_id = null, $grado_id = null, $seccion_id = null) {
	    
	    $sql = "SELECT COUNT(*) AS total 
	            FROM INSCRIPCION i
	            INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id";

	    $condiciones = [];
	    $params = [];

	    if (!empty($periodo_id)) {
	        $condiciones[] = "i.periodo_academico_id = :periodo_id";
	        $params['periodo_id'] = $periodo_id;
	    }
	    if (!empty($grado_id)) {
	        $condiciones[] = "gs.grado_id = :grado_id";
	        $params['grado_id'] = $grado_id;
	    }
	    if (!empty($seccion_id)) {
	        $condiciones[] = "gs.seccion_id = :seccion_id";
	        $params['seccion_id'] = $seccion_id;
	    }

	    if (count($condiciones) > 0) {
	        $sql .= " WHERE " . implode(" AND ", $condiciones);
	    }

	    $stmt = $this->db->prepare($sql);
	    $stmt->execute($params);
	    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

	    return (int)$resultado['total'];
	}
	public function filtrar_todas_las_inscripciones($periodo_id = null, $grado_id = null, $seccion_id = null) {

	    $sql = "SELECT 
            i.id AS id_inscripcion,
            pe.nombre_1, pe.apellido_1, pe.nacionalidad, 
            pe.cedula_identidad, e.cedula_escolar, e.estado,
            s.nombre AS seccion, g.nombre AS grado, pa.nombre AS periodo
        FROM INSCRIPCION i
        INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
        INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
        INNER JOIN GRADO g ON gs.grado_id = g.id
        INNER JOIN SECCION s ON gs.seccion_id = s.id
        INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
        INNER JOIN PERSONA pe ON e.persona_id = pe.id";

	    $condiciones = [];
	    $params = [];

	    if (!empty($periodo_id)) {
	        $condiciones[] = "i.periodo_academico_id = :periodo_id";
	        $params['periodo_id'] = $periodo_id;
	    }

	    if (!empty($grado_id)) {
	        $condiciones[] = "gs.grado_id = :grado_id";
	        $params['grado_id'] = $grado_id;
	    }

	    if (!empty($seccion_id)) {
	        $condiciones[] = "gs.seccion_id = :seccion_id";
	        $params['seccion_id'] = $seccion_id;
	    }

	    if (count($condiciones) > 0) {
	        $sql .= " WHERE " . implode(" AND ", $condiciones);
	    }

	    // Ordenamos por fecha de inscripción sin limitar la cantidad de resultados
	    $sql .= " ORDER BY i.fecha_inscripcion DESC";

	    $stmt = $this->db->prepare($sql);
	    $stmt->execute($params);

	    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}






}
?>
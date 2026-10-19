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
            SELECT 
                i.*, 
                p.nacionalidad, p.cedula_identidad, p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2, p.fecha_nacimiento, p.sexo,
                e.cedula_escolar, e.numero_hijo, e.lateralidad, e.estado AS estado_estudiante,
                g.nombre AS grado, s.nombre AS seccion, pa.nombre AS periodo, na.nombre AS nivel_academico, i.tipo_ingreso, i.colegio_procedencia,
                ant.estatura, ant.peso, ant.talla_camisa, ant.talla_pantalon, ant.talla_zapato,
                sal.reacciones_alergicas, sal.cuales_alergias, sal.enfermedades_padecidas, sal.atencion_especial, sal.horario_tratamiento,
                ext.realiza_deportes, ext.cuales_deportes, ext.posee_canaima, ext.serial_canaima, ext.estado_canaima,
                -- Representante Principal y su Dirección
                rep.parentesco AS rep_parentesco, rep.ingreso_mensual AS rep_ingreso, rep.telefono_movil AS rep_telefono, rep.profesion AS rep_profesion,
                rep_p.nacionalidad AS rep_nacionalidad, rep_p.cedula_identidad AS rep_cedula, rep_p.nombre_1 AS rep_nombre_1, rep_p.apellido_1 AS rep_apellido_1,
                es.nombre AS estado_nombre, m.nombre AS municipio_nombre, pr.nombre AS parroquia_nombre,
                d.sector, d.avenida, d.calle, d.numero_vivienda, d.tipo_vivienda,
                -- Representante Secundario, su Profesión, Ingresos y su Dirección (d2)
                rep2.parentesco AS rep2_parentesco, rep2.ingreso_mensual AS rep2_ingreso, rep2.telefono_movil AS rep2_telefono, rep2.profesion AS rep2_profesion,
                rep2_p.nacionalidad AS rep2_nacionalidad, rep2_p.cedula_identidad AS rep2_cedula, rep2_p.nombre_1 AS rep2_nombre_1, rep2_p.apellido_1 AS rep2_apellido_1,
                es2.nombre AS rep2_estado_nombre, m2.nombre AS rep2_municipio_nombre, pr2.nombre AS rep2_parroquia_nombre,
                d2.sector AS rep2_sector, d2.avenida AS rep2_avenida, d2.calle AS rep2_calle, d2.numero_vivienda AS rep2_numero_vivienda, d2.tipo_vivienda AS rep2_tipo_vivienda
            FROM INSCRIPCION i
            INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
            INNER JOIN PERSONA p ON e.persona_id = p.id
            INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
            INNER JOIN GRADO g ON gs.grado_id = g.id
            INNER JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
            INNER JOIN SECCION s ON gs.seccion_id = s.id
            INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
            LEFT JOIN ANTROPOMETRICO ant ON e.antropometrico_id = ant.id
            LEFT JOIN SALUD sal ON e.salud_id = sal.id
            LEFT JOIN EXTRA_CURRICULAR ext ON e.extra_curricular_id = ext.id
            -- Principal
            LEFT JOIN PERSONA_REPRESENTANTE rep ON e.representante_principal_id = rep.id
            LEFT JOIN PERSONA rep_p ON rep.persona_id = rep_p.id
            LEFT JOIN DIRECCION d ON rep_p.direccion_id = d.id
            LEFT JOIN PARROQUIA pr ON d.parroquia_id = pr.id
            LEFT JOIN MUNICIPIO m ON pr.municipio_id = m.id
            LEFT JOIN ESTADO es ON m.estado_id = es.id
            -- Secundario
            LEFT JOIN PERSONA_REPRESENTANTE rep2 ON e.representante_secundario_id = rep2.id
            LEFT JOIN PERSONA rep2_p ON rep2.persona_id = rep2_p.id
            LEFT JOIN DIRECCION d2 ON rep2_p.direccion_id = d2.id
            LEFT JOIN PARROQUIA pr2 ON d2.parroquia_id = pr2.id
            LEFT JOIN MUNICIPIO m2 ON pr2.municipio_id = m2.id
            LEFT JOIN ESTADO es2 ON m2.estado_id = es2.id
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

	public function filtrar_inscripciones($filtros = [], $pagina = 1, $limite = 15) {
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
                INNER JOIN PERSONA pe ON e.persona_id = pe.id
                LEFT JOIN DIRECCION d ON pe.direccion_id = d.id
                LEFT JOIN PARROQUIA pr ON d.parroquia_id = pr.id
                LEFT JOIN MUNICIPIO m ON pr.municipio_id = m.id
                LEFT JOIN ESTADO es ON m.estado_id = es.id
                LEFT JOIN SALUD sal ON e.salud_id = sal.id
                LEFT JOIN EXTRA_CURRICULAR ext ON e.extra_curricular_id = ext.id
                LEFT JOIN PERSONA_REPRESENTANTE rep ON e.representante_principal_id = rep.id";

        list($sql_where, $params) = $this->construir_where_filtros($filtros);

        $sql .= $sql_where . " ORDER BY i.fecha_inscripcion DESC LIMIT {$limite} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar_total_inscripciones($filtros = []) {
        $sql = "SELECT COUNT(*) AS total 
                FROM INSCRIPCION i
                INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
                INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
                INNER JOIN PERSONA pe ON e.persona_id = pe.id
                LEFT JOIN DIRECCION d ON pe.direccion_id = d.id
                LEFT JOIN PARROQUIA pr ON d.parroquia_id = pr.id
                LEFT JOIN MUNICIPIO m ON pr.municipio_id = m.id
                LEFT JOIN ESTADO es ON m.estado_id = es.id
                LEFT JOIN SALUD sal ON e.salud_id = sal.id
                LEFT JOIN EXTRA_CURRICULAR ext ON e.extra_curricular_id = ext.id
                LEFT JOIN PERSONA_REPRESENTANTE rep ON e.representante_principal_id = rep.id";

        list($sql_where, $params) = $this->construir_where_filtros($filtros);

        $sql .= $sql_where;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    public function filtrar_todas_las_inscripciones($filtros = []) {
        $sql = "SELECT 
                    i.id AS id_inscripcion,
                    pe.nombre_1, pe.nombre_2, pe.apellido_1, pe.apellido_2, pe.nacionalidad, 
                    pe.cedula_identidad, e.cedula_escolar, e.estado,
                    s.nombre AS seccion, g.nombre AS grado, pa.nombre AS periodo,
                    -- Datos del Representante (Uniendo con su respectiva tabla PERSONA)
                    rep_p.nacionalidad AS rep_nacionalidad,
                    rep_p.cedula_identidad AS rep_cedula,
                    rep_p.nombre_1 AS rep_nombre,
                    rep_p.apellido_1 AS rep_apellido,
                    rep.profesion,
                    rep.ingreso_mensual,
                    -- Datos de Ubicación Geográfica
                    es.nombre AS estado_nombre,
                    m.nombre AS municipio_nombre,
                    pr.nombre AS parroquia_nombre
                FROM INSCRIPCION i
                INNER JOIN PERIODO_ACADEMICO pa ON i.periodo_academico_id = pa.id
                INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
                INNER JOIN GRADO g ON gs.grado_id = g.id
                INNER JOIN SECCION s ON gs.seccion_id = s.id
                INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
                INNER JOIN PERSONA pe ON e.persona_id = pe.id
                LEFT JOIN SALUD sal ON e.salud_id = sal.id
                LEFT JOIN EXTRA_CURRICULAR ext ON e.extra_curricular_id = ext.id
                LEFT JOIN PERSONA_REPRESENTANTE rep ON e.representante_principal_id = rep.id
                LEFT JOIN PERSONA rep_p ON rep.persona_id = rep_p.id
                LEFT JOIN DIRECCION d ON rep_p.direccion_id = d.id
                LEFT JOIN PARROQUIA pr ON d.parroquia_id = pr.id
                LEFT JOIN MUNICIPIO m ON pr.municipio_id = m.id
                LEFT JOIN ESTADO es ON m.estado_id = es.id
                ";
                
        list($sql_where, $params) = $this->construir_where_filtros($filtros);

        $sql .= $sql_where . " ORDER BY i.fecha_inscripcion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_inscripciones_por_grado_seccion($periodo_activo_id) {
        $sql = "SELECT 
                    g.nombre AS grado,
                    s.nombre AS seccion,
                    COUNT(i.id) AS total_estudiantes
                FROM GRADO_SECCION gs
                INNER JOIN GRADO g ON gs.grado_id = g.id
                INNER JOIN SECCION s ON gs.seccion_id = s.id
                LEFT JOIN INSCRIPCION i ON i.grado_seccion_id = gs.id AND i.periodo_academico_id = :periodo_id
                WHERE gs.estado = 'Activo'
                GROUP BY gs.id, g.nombre, s.nombre
                ORDER BY g.id, s.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_general_estudiantes = 0;
        foreach ($resultados as $fila) {
            $total_general_estudiantes += (int)$fila['total_estudiantes'];
        }
        
        foreach ($resultados as &$fila) {
            $total = (int)$fila['total_estudiantes'];
            
            if ($total_general_estudiantes > 0) {
                $porcentaje = ($total / $total_general_estudiantes) * 100;
                $fila['porcentaje_distribucion'] = round($porcentaje, 2);
            } else {
                $fila['porcentaje_distribucion'] = 0;
            }
            
            $fila['total_general_periodo'] = $total_general_estudiantes;
        }

        return $resultados;
    }

	private function construir_where_filtros($filtros) {
        $condiciones = [];
        $params = [];

        // 1. Académico y Tipo de Ingreso
        if (!empty($filtros['periodo_id'])) {
            $condiciones[] = "i.periodo_academico_id = :periodo_id";
            $params['periodo_id'] = $filtros['periodo_id'];
        }
        if (!empty($filtros['grado_id'])) {
            $condiciones[] = "gs.grado_id = :grado_id";
            $params['grado_id'] = $filtros['grado_id'];
        }
        if (!empty($filtros['seccion_id'])) {
            $condiciones[] = "gs.seccion_id = :seccion_id";
            $params['seccion_id'] = $filtros['seccion_id'];
        }
        if (!empty($filtros['tipo_ingreso'])) {
            $condiciones[] = "i.tipo_ingreso = :tipo_ingreso";
            $params['tipo_ingreso'] = $filtros['tipo_ingreso'];
        }

        // 2. Ubicación Geográfica
        if (!empty($filtros['estado_id'])) {
            $condiciones[] = "es.id = :estado_id";
            $params['estado_id'] = $filtros['estado_id'];
        }
        if (!empty($filtros['municipio_id'])) {
            $condiciones[] = "m.id = :municipio_id";
            $params['municipio_id'] = $filtros['municipio_id'];
        }
        if (!empty($filtros['parroquia_id'])) {
            $condiciones[] = "d.parroquia_id = :parroquia_id";
            $params['parroquia_id'] = $filtros['parroquia_id'];
        }

        // 3. Salud y Antropometría
        if (!empty($filtros['atencion_especial'])) {
            $condiciones[] = "sal.atencion_especial = :atencion_especial";
            $params['atencion_especial'] = $filtros['atencion_especial'];
        }
        if (!empty($filtros['posee_alergias'])) {
            $condiciones[] = "sal.reacciones_alergicas = :posee_alergias";
            $params['posee_alergias'] = $filtros['posee_alergias'];
        }
        if (!empty($filtros['enfermedad'])) {
            $condiciones[] = "(sal.enfermedades_padecidas LIKE :enfermedad OR sal.cuales_alergias LIKE :enfermedad)";
            $params['enfermedad'] = "%" . $filtros['enfermedad'] . "%";
        }

        // 4. Perfil Socioeconómico y Recursos
        if (!empty($filtros['profesion'])) {
            $condiciones[] = "rep.profesion = :profesion";
            $params['profesion'] = $filtros['profesion'];
        }
        if (!empty($filtros['rango_ingreso'])) {
            if ($filtros['rango_ingreso'] === '0-100') {
                $condiciones[] = "rep.ingreso_mensual < 100";
            } elseif ($filtros['rango_ingreso'] === '100-300') {
                $condiciones[] = "rep.ingreso_mensual BETWEEN 100 AND 300";
            } elseif ($filtros['rango_ingreso'] === '301-500') {
                $condiciones[] = "rep.ingreso_mensual BETWEEN 301 AND 500";
            } elseif ($filtros['rango_ingreso'] === '500+') {
                $condiciones[] = "rep.ingreso_mensual > 500";
            }
        }
        if (!empty($filtros['estado_canaima'])) {
            $condiciones[] = "ext.estado_canaima = :estado_canaima";
            $params['estado_canaima'] = $filtros['estado_canaima'];
        }
        if (!empty($filtros['realiza_deportes'])) {
            $condiciones[] = "ext.realiza_deportes = :realiza_deportes";
            $params['realiza_deportes'] = $filtros['realiza_deportes'];
        }

        $sql_where = count($condiciones) > 0 ? " WHERE " . implode(" AND ", $condiciones) : "";

        return [$sql_where, $params];
    }





}
?>
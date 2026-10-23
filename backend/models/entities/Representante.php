<?php  
require_once __DIR__ . '/utils.php';

trait Representante {
    use Utils;

	public function guardar_representante($representante) {

		$llave_direccion = $this->insertar('DIRECCION', $representante['direccion']);

		$llave_persona = $this->insertar('PERSONA', [...$representante['persona'], 'direccion_id' => $llave_direccion]);

		return $this->insertar('PERSONA_REPRESENTANTE', [
				...$representante['persona_representante'],
			 	'persona_id' => $llave_persona
			]
		);
	}

	public function actualizar_representante($representante, $id) {

		$representante_id = $this->buscar_valor('id', 'PERSONA_REPRESENTANTE', 'persona_id', $id);

		$direccion_id = $this->buscar_valor('direccion_id', 'PERSONA', 'id', $id);

		$this->actualizar('PERSONA_REPRESENTANTE', $representante['persona_representante'], 'id', $representante_id);

		$this->actualizar('DIRECCION', $representante['direccion'], 'id', $direccion_id);

		return $representante_id;

	}

    public function obtener_datos_representante($clave, $valor){
        $persona_representante = [];
        $persona = [];
        $direccion = [];

        if ($clave === 'cedula_identidad') {
            $persona = $this->buscar_todo('PERSONA', $clave, $valor);
            if (!empty($persona)) $persona_representante = $this->buscar_todo('PERSONA_REPRESENTANTE', 'persona_id', $persona['id']);
        } else {
            $persona_representante = $this->buscar_todo('PERSONA_REPRESENTANTE', $clave, $valor);
            if (!empty($persona_representante)) $persona =  $this->buscar_todo('PERSONA', 'id', $persona_representante['persona_id']);
        }

        if (empty($persona) || empty($persona_representante)) return;
        
        $stmt = $this->db->prepare("
            SELECT d.*, p.id as parroquia_id, m.id as municipio_id, e.id as estado_id
            FROM DIRECCION d 
            INNER JOIN PARROQUIA p ON p.id = d.parroquia_id
            INNER JOIN MUNICIPIO m ON m.id = p.municipio_id
            INNER JOIN ESTADO e ON e.id = m.estado_id
            WHERE d.id = :direccion_id LIMIT 1
        ");

        $stmt->execute(['direccion_id' => $persona['direccion_id']]);
        $direccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'persona_representante' => $persona_representante,
            'persona' => $persona,
            'direccion' => $direccion
        ];
    }

    public function obtener_representante_completo($clave, $valor) {
        $sql = "SELECT 
                    pr.id AS representante_id, pr.parentesco, pr.ingreso_mensual, pr.telefono_movil, 
                    pr.correo_electronico, pr.profesion, pr.estado_civil, 
                    pr.empresa_trabajo, p.direccion_id,
                    p.id AS persona_id, p.nacionalidad, p.cedula_identidad, p.nombre_1, p.nombre_2, 
                    p.apellido_1, p.apellido_2, p.fecha_nacimiento, p.sexo
                FROM PERSONA_REPRESENTANTE pr
                INNER JOIN PERSONA p ON pr.persona_id = p.id
                WHERE pr.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clave => $valor]);
        $rep_completo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rep_completo) {
            return null;
        }

        return [
            'persona_representante' => [
                'id' => $rep_completo['representante_id'],
                'parentesco' => $rep_completo['parentesco'],
                'ingreso_mensual' => $rep_completo['ingreso_mensual'],
                'telefono_movil' => $rep_completo['telefono_movil'],
                'correo_electronico' => $rep_completo['correo_electronico'],
                'profesion' => $rep_completo['profesion'],
                'estado_civil' => $rep_completo['estado_civil'],
                'empresa_trabajo' => $rep_completo['empresa_trabajo'],
                'direccion_id' => $rep_completo['direccion_id']
            ],
            'persona' => [
                'id' => $rep_completo['persona_id'],
                'nacionalidad' => $rep_completo['nacionalidad'],
                'cedula_identidad' => $rep_completo['cedula_identidad'],
                'nombre_1' => $rep_completo['nombre_1'],
                'nombre_2' => $rep_completo['nombre_2'],
                'apellido_1' => $rep_completo['apellido_1'],
                'apellido_2' => $rep_completo['apellido_2'],
                'fecha_nacimiento' => $rep_completo['fecha_nacimiento'],
                'sexo' => $rep_completo['sexo']
            ]
        ];
    }

    public function obtener_ingresos_mensuales_representantes($clave = null, $condicion = null) {
        $sql = "SELECT ingreso_mensual FROM PERSONA_REPRESENTANTE";
        $parametros = [];

        if (!empty($clave) && !empty($condicion)) {
            $sql .= " WHERE $clave = :condicion"; 
            $parametros = [$clave => $condicion];
        }

        $stmt =  $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);         
    }

    public function obtener_profesiones($limit = null) {
        $sql = "SELECT profesion as nombre, profesion as id 
            FROM PERSONA_REPRESENTANTE 
            WHERE estado = 'Activo' 
            AND profesion IS NOT NULL 
            AND profesion != '' 
            GROUP BY profesion  
        ";

        if ($limit != null && is_numeric($limit)) $sql .= " LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);

        if ($limit != null && is_numeric($limit)) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);        

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_top_profesiones($limit = null) {
        $sql = "SELECT profesion, COUNT(*) as total 
            FROM PERSONA_REPRESENTANTE 
            WHERE estado = 'Activo' 
            AND profesion IS NOT NULL 
            AND profesion != '' 
            GROUP BY profesion 
            ORDER BY total DESC 
        ";

        if ($limit != null && is_numeric($limit)) $sql .= " LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);

        if ($limit != null && is_numeric($limit)) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);        

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_rangos_ingresos() {
        $sql = "SELECT 
                    CASE 
                        WHEN ingreso_mensual < 100 THEN 'Menos de $100'
                        WHEN ingreso_mensual BETWEEN 100 AND 300 THEN '$100 - $300'
                        WHEN ingreso_mensual BETWEEN 301 AND 500 THEN '$301 - $500'
                        ELSE 'Más de $500'
                    END AS etiqueta,
                    COUNT(*) AS cantidad
                FROM PERSONA_REPRESENTANTE
                WHERE estado = 'Activo' 
                  AND ingreso_mensual IS NOT NULL
                GROUP BY etiqueta
                ORDER BY 
                    CASE etiqueta
                        WHEN 'Menos de $100' THEN 1
                        WHEN '$100 - $300' THEN 2
                        WHEN '$301 - $500' THEN 3
                        ELSE 4
                    END";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_top_sectores($periodo_activo_id, $limit = 5) {
        $sql = "SELECT 
                    d.sector, 
                    COUNT(*) AS total
                FROM INSCRIPCION i
                INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                INNER JOIN PERSONA p ON pe.persona_id = p.id
                INNER JOIN DIRECCION d ON p.direccion_id = d.id
                WHERE i.periodo_academico_id = :periodo_id 
                  AND pe.estado = 'Activo'
                  AND d.sector IS NOT NULL 
                  AND TRIM(d.sector) != ''
                GROUP BY d.sector
                ORDER BY total DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':periodo_id', $periodo_activo_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_tipos_vivienda($periodo_activo_id) {
        $sql = "SELECT 
                    d.tipo_vivienda, 
                    COUNT(*) AS total
                FROM INSCRIPCION i
                INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                INNER JOIN PERSONA p ON pe.persona_id = p.id
                INNER JOIN DIRECCION d ON p.direccion_id = d.id
                WHERE i.periodo_academico_id = :periodo_id 
                  AND pe.estado = 'Activo'
                  AND d.tipo_vivienda IS NOT NULL
                GROUP BY d.tipo_vivienda
                ORDER BY total DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_condiciones_vivienda($periodo_activo_id) {
        $sql = "SELECT 
                    d.condicion_vivienda, 
                    COUNT(*) AS total
                FROM INSCRIPCION i
                INNER JOIN PERSONA_ESTUDIANTE pe ON i.estudiante_id = pe.id
                INNER JOIN PERSONA p ON pe.persona_id = p.id
                INNER JOIN DIRECCION d ON p.direccion_id = d.id
                WHERE i.periodo_academico_id = :periodo_id 
                  AND pe.estado = 'Activo'
                  AND d.condicion_vivienda IS NOT NULL
                GROUP BY d.condicion_vivienda
                ORDER BY total DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['periodo_id' => $periodo_activo_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	public function buscar_coincidencias_representante($busqueda, $tipo_busqueda = 'representante' ) {

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
            INNER JOIN INSCRIPCION ins ON pe.id = ins.estudiante_id
            INNER JOIN GRADO_SECCION gs ON ins.grado_seccion_id = gs.id
            WHERE 1=1
        ";

        if (!empty($busqueda['periodo_academico_id'])) {
            $sql .= " AND ins.periodo_academico_id = :periodo_academico_id";
            $parametros[':periodo_academico_id'] = $busqueda['periodo_academico_id'];
        } else {
            $sql .= " AND ins.id = (SELECT MAX(i_sub.id) FROM INSCRIPCION i_sub WHERE i_sub.estudiante_id = pe.id)";
        }

        if (!empty($busqueda['grado_id'])) {
            $sql .= " AND gs.grado_id = :grado_id";
            $parametros[':grado_id'] = $busqueda['grado_id'];
        }

        if (!empty($busqueda['seccion_id'])) {
            $sql .= " AND gs.seccion_id = :seccion_id";
            $parametros[':seccion_id'] = $busqueda['seccion_id'];
        }

        if (!empty($busqueda['estado'])) {
            $sql .= " AND pe.estado = :estado";
            $parametros[':estado'] = $busqueda['estado'];
        }

        if (!empty($busqueda['busqueda_general'])) {

            $palabras = array_filter(explode(' ', trim($busqueda['busqueda_general'])));
            
            $alias = ($tipo_busqueda == 'representante') ? 'p_rep' : 'p_est';
            $condiciones_palabras = [];

            foreach ($palabras as $index => $palabra) {
                if (empty($palabra)) continue;
                $key = ":busqueda_general_{$index}";
                
                $condiciones_palabras[] = "(
                    {$alias}.cedula_identidad LIKE {$key} 
                    OR {$alias}.nombre_1 LIKE {$key} 
                    OR {$alias}.nombre_2 LIKE {$key}
                    OR {$alias}.apellido_1 LIKE {$key} 
                    OR {$alias}.apellido_2 LIKE {$key}
                )";

                $parametros[$key] = "%" . $palabra . "%";
            }

            if (!empty($condiciones_palabras)) {
                $sql .= " AND (" . implode(" AND ", $condiciones_palabras) . ")";
            }
        }
    }
}

?>
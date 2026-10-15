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
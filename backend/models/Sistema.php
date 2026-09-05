<?php

class Sistema {
    private $db;
    
    function __construct($db){
        $this->db = $db;
    }

    public function obtener_parametros_formulario() {
        return [
            'periodos' => $this->db->query('SELECT * FROM PERIODO_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),

            'secciones' => $this->db->query('SELECT * FROM SECCION')->fetchAll(PDO::FETCH_ASSOC),
            'grados'  => $this->db->query('SELECT * FROM GRADO')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO')->fetchAll(PDO::FETCH_ASSOC),

            'grados_secciones' => $this->db->query('
                SELECT gs.id, gs.grado_id, gs.seccion_id, na.id AS nivel_academico_id FROM GRADO_SECCION gs
                JOIN GRADO g ON gs.grado_id = g.id
                JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
                ORDER BY gs.id ASC
            ')->fetchAll(PDO::FETCH_ASSOC),
            'paises'  => $this->db->query('SELECT * FROM PAIS')->fetchAll(PDO::FETCH_ASSOC),
            'estados'  => $this->db->query('SELECT * FROM ESTADO')->fetchAll(PDO::FETCH_ASSOC),
            'municipios' => $this->db->query('SELECT * FROM MUNICIPIO')->fetchAll(PDO::FETCH_ASSOC),
            'parroquias' => $this->db->query('SELECT * FROM PARROQUIA')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function obtener_periodos_academicos() {
        return $this->db->query('SELECT * FROM PERIODO_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_niveles_academicos() {
        return $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_grados() {
        return [
            'grados'  => $this->db->query('SELECT * FROM GRADO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
    
    public function obtener_secciones() {
        return  $this->db->query('SELECT * FROM SECCION ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_grados_secciones() {
        return [
            'secciones' => $this->db->query('SELECT * FROM SECCION ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC),
            'grados'  => $this->db->query('SELECT * FROM GRADO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'niveles_academicos'  => $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
            'grados_secciones' => $this->db->query('
                SELECT gs.id, gs.grado_id, gs.seccion_id, gs.estado, na.id AS nivel_academico_id FROM GRADO_SECCION gs
                JOIN GRADO g ON gs.grado_id = g.id
                JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
                ORDER BY gs.id DESC
            ')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function buscar_coincidencias($busqueda, $tipo_busqueda = 'estudiante' ) {

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

        $entidad = $tipo_busqueda == 'representante' ? 'p_rep' : 'p_est';
        $sql .= " GROUP BY p_rep.id, p_est.id ORDER BY {$entidad}.nombre_1 ASC, {$entidad}.nombre_2 ASC, {$entidad}.apellido_1 ASC, {$entidad}.apellido_2 ASC";

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute($parametros);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_historial_estudiante($id) {
        $resultado = [];

        $estudiante =  $this->buscar_todo('PERSONA_ESTUDIANTE', 'id', $id);

        $resultado['estudiante'] = [
            'estudiante' => $estudiante,
            'persona' =>  $this->buscar_todo('PERSONA', 'id', $estudiante['persona_id']),
            'antropometricos' => $this->buscar_todo('ANTROPOMETRICO', 'id', $estudiante['antropometrico_id']),
            'salud' =>  $this->buscar_todo('SALUD', 'id', $estudiante['salud_id'] ),
            'extra_curriculares' =>  $this->buscar_todo('EXTRA_CURRICULAR', 'id', $estudiante['extra_curricular_id'])
        ];

        $representante_principal = $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_principal_id']);
        $resultado['representante_principal'] = [
            'persona' => $representante_principal,
            'representante' =>    $this->buscar_todo('PERSONA', 'id', $representante_principal['persona_id']),
        ];

        if (!empty($estudiante['representante_secundario_id'])) {
            $representante_secundario = $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_secundario_id']);
            $resultado['representante_secundario'] = [
                'persona' => $representante_secundario,
                'representante' => $this->buscar_todo('PERSONA', 'id', $representante_secundario['persona_id'])
            ];
        }

        return $resultado;

    }  

    public function crear_primer_admin($nombre, $pass) {
        $tabla_llena = ($this->db->query("SELECT 1 FROM USUARIO LIMIT 1")->fetch());

        if ($tabla_llena) {
            return false; 
        }   

        $stmt = $this->db->prepare("INSERT INTO USUARIO (nombre, pass, rol) VALUES (:nombre, :pass, :rol)");

        $resultado = $stmt->execute([
            'nombre'=> $nombre,
            'pass' => $pass,
            'rol' => 'Administrador'
        ]);

        if($resultado) {
            return $this->db->lastInsertId();
        }
    }

    public function buscar_usuario($nombre, $pass) {
        $stmt = $this->db->prepare("SELECT id, nombre, rol FROM USUARIO WHERE nombre = :nombre AND pass = :pass");
        $stmt->execute(['nombre' => $nombre, 'pass' => $pass]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }




    public function buscar_valor($columna, $tabla, $clave, $valor) {
        $stmt = $this->db->prepare("SELECT $columna FROM $tabla WHERE $clave = :valor LIMIT 1");

        $stmt->execute(['valor' => $valor]);

        return $stmt->fetchColumn();
    }

    public function buscar_todo($tabla, $clave, $valor) {
        if (!$valor) return;

        $stmt = $this->db->prepare("SELECT * FROM $tabla WHERE $clave = :valor");

        $stmt->execute(['valor' => $valor]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } 

    public function existe($tabla, $campo, $valor) {

        if (!is_array($valor)) {
            $valor = [$valor];
        }

        $campos = array_map('trim', explode(',', $campo));

        $clausulas = [];
        $params = [];

        foreach ($campos as $index => $nombreCampo) {
            $paramKey = ":valor_{$index}";
            $clausulas[] = "{$nombreCampo} = {$paramKey}";
            $params[$paramKey] = $valor[$index];
        }

        $whereSQL = implode(' AND ', $clausulas);
        $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$whereSQL}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

}
?>
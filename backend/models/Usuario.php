
<?php

Class Usuario{
	
	protected $db;

	public $type = 'Usuario';

	function __construct($db){
		$this->db = $db;
	}


  	//=== Metodos de transacciones ===

	public function iniciar_transaccion() { $this->db->beginTransaction(); }

	public function confirmar_transaccion() { $this->db->commit(); }

	public function revertir_transaccion() {
		if ($this->db->inTransaction()) $this->db->rollBack();
	}


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

	public function listar_estudiantes($limit = '') {
		$consulta =  "SELECT i.id AS id_inscripcion, i.fecha_inscripcion,
          p.nombre_1, p.apellido_1, p.nacionalidad, p.cedula_identidad,
          e.cedula_escolar, s.nombre AS seccion, g.nombre AS grado
          FROM INSCRIPCION i
          INNER JOIN GRADO_SECCION gs ON i.grado_seccion_id = gs.id
          INNER JOIN GRADO g ON gs.grado_id = g.id
          INNER JOIN SECCION s ON gs.seccion_id = s.id
          INNER JOIN PERSONA_ESTUDIANTE e ON i.estudiante_id = e.id
          INNER JOIN PERSONA p ON e.persona_id = p.id

          ORDER BY i.fecha_inscripcion DESC";

        if (!empty($limit)) {
         	$consulta = $consulta . " LIMIT {$limit}";
        }

		$stmt = $this->db->query($consulta);
        
		return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}


	//=== Metodos para el manejo de datos de representantes ===

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


	//=== Metodos ramdoms que no supe en que categoria meter (pero si sirven) ===

	public function obtener_historial_estudiante ($cedula_identidad) {
		
		$persona = $this->buscar_todo('PERSONA', 'cedula_identidad', $cedula_identidad);
		$estudiante = $persona ? $this->buscar_todo('PERSONA_ESTUDIANTE', 'persona_id', $persona['id']): null; 

		if(!$estudiante) return null;

		$antropometrico = $this->buscar_todo('ANTROPOMETRICO', 'id', $estudiante['antropometrico_id']);

		$salud = $this->buscar_todo('SALUD', 'id', $estudiante['salud_id']);
		$extra_curricular = $this->buscar_todo('EXTRA_CURRICULAR', 'id', $estudiante['extra_curricular_id']);
		
		$representante_principal = $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_principal_id']);
		$persona_r_principal = $this->buscar_todo('PERSONA', 'id', $representante_principal['persona_id']);
		$direccion_r_principal = $this->buscar_todo('DIRECCION', 'id', $persona_r_principal['direccion_id']);

		$representante_secundario = $estudiante['representante_secundario_id'] ? $this->buscar_todo('PERSONA_REPRESENTANTE', 'id', $estudiante['representante_secundario_id']): null;

		$persona_r_secundario = $representante_secundario ? $this->buscar_todo('PERSONA', 'id', $representante_secundario['persona_id']): null;

		$direccion_r_secundario = $persona_r_secundario ? $this->buscar_todo('DIRECCION', 'id', $persona_r_secundario['direccion_id']): null;

		return [
			'estudiante' => [
				'persona' => $persona,
				'persona_estudiante' => $estudiante,
				'antropometrico' => $antropometrico,
				'salud' => $salud,
				'extra_curricular' => $extra_curricular
			], 
			'representante_principal' => [
				'persona' => $persona_r_principal,
				'persona_representante' => $representante_principal,
				'direccion' => $direccion_r_principal
			],
			'representante_secundario' => [
				'persona' => $persona_r_secundario,
				'persona_representante' => $representante_secundario,
				'direccion' => $direccion_r_secundario
			]  
		];

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

	public function existe($tabla, $clave, $valor) {

		$stmt = $this->db->prepare("SELECT 1 FROM $tabla WHERE $clave = :valor LIMIT 1");

		$stmt->execute(['valor' => $valor]);

		return (int )$stmt->fetchColumn() === 1;
	}


	//=== Metodos privados de ===


	//=== Crear y Actualizar ===

	protected function insertar($tabla, $datos) {

		$columna = implode(', ', array_keys($datos));
		$values = ':'. implode(', :', array_keys($datos));

		$stmt = $this->db->prepare("INSERT INTO $tabla ($columna) VALUES ($values)");

		if(!$stmt->execute($datos)){
			return false;
		};

		$id = $this->db->lastInsertId();

		return ($id && $id !== '0') ? $id : true;
	}

	protected function actualizar($tabla, $datos, $clave, $valor) {

		$paquete = [];

		foreach (array_keys($datos) as $key) {
			$paquete[] = "{$key} = :{$key}";
		}

		$columnas = implode(', ', $paquete);

		$stmt = $this->db->prepare("UPDATE {$tabla} SET {$columnas} WHERE $clave = :valor");

		$stmt->execute([...$datos, 'valor' => $valor]);
	}


}

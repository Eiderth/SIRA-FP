
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
		return "{$data['estudiante']['nacionalidad']}{$data['estudiante']['fecha_nacimiento'][2]}{$data['estudiante']['fecha_nacimiento'][3]}{$data['estudiante']['numero_hijo']}{$data['representante_principal']['cedula']}";
	}

	public function procesar_periodo_inscripcion($data, $estudiante_cedula_escolar) {

		return $this->insertar('inscripciones', [
			...$data['inscripcion'],
			'estudiante_cedula_escolar' => $estudiante_cedula_escolar,
			'periodo_id' => $data['periodo']['id']
		]);

	}

	public function verificar_inscripcion ($cedula_identidad, $cedula_escolar , $periodo_id) {

		$stmt = $this->db->prepare("
			SELECT 1 FROM inscripciones i
			JOIN estudiantes e ON i.estudiante_cedula_escolar = e.cedula_escolar
			JOIN periodos_academicos p ON i.periodo_id = p.id
			WHERE (e.cedula_identidad = :cedula_identidad OR e.cedula_escolar = :cedula_escolar)
			AND p.id = :periodo_id LIMIT 1
		");

		$stmt->execute([
			'cedula_identidad' => $cedula_identidad,
			'cedula_escolar' => $cedula_escolar,
			'periodo_id' => $periodo_id
		]);

		return (int) $stmt->fetchColumn() === 1;
	}

	public function buscar_planilla_inscripcion($id){
		$stmt = $this->db->prepare("
	      SELECT i.*, e.*, g.nombre AS grado, s.nombre AS seccion, p.nombre AS periodo
	      FROM inscripciones i
	      INNER JOIN estudiantes e ON i.estudiante_cedula_escolar = e.cedula_escolar
	      INNER JOIN grados g ON i.grado_asignado_id = g.id
	      INNER JOIN secciones s ON i.seccion_asignada_id = s.id
	      INNER JOIN periodos_academicos p ON i.periodo_id = p.id
	      WHERE i.id = :id LIMIT 1
	    ");
	    $stmt->execute(['id' => $id]);
	    return $stmt->fetch(PDO::FETCH_ASSOC);
	}


	//=== Metodos para el manejo de datos de estudiantes ===

	public function guardar_estudiante ($data) {

		$llave_antropometricos = $this->insertar('antropometricos', $data['antropometricos']);

		$llave_salud = $this->insertar('salud', $data['salud']);

		$llave_extra_curriculares = $this->insertar('extra_curriculares', $data['extra_curriculares']);

		return $this->insertar('estudiantes', [
			...$data['estudiante'],
			'antropometricos_id' => $llave_antropometricos,
			'salud_id' => $llave_salud,
			'extra_curriculares_id' => $llave_extra_curriculares
		]);
	}

	public function actualizar_estudiante($data) {

		$cedula_escolar = $data['estudiante']['cedula_escolar'];

		unset($data['estudiante']['cedula_escolar']);
		unset($data['estudiante']['cedula_identidad']);

		$this->actualizar('estudiantes', $data['estudiante'], 'cedula_escolar', $cedula_escolar);

		$antropometricos_id = $this->buscar_valor('antropometricos_id', 'estudiantes', 'cedula_escolar', $cedula_escolar);

		$salud_id = $this->buscar_valor('salud_id', 'estudiantes', 'cedula_escolar', $cedula_escolar);

		$extra_curriculares_id = $this->buscar_valor('extra_curriculares_id', 'estudiantes', 'cedula_escolar', $cedula_escolar);

		$this->actualizar('antropometricos', $data['antropometricos'], 'id' , $antropometricos_id);

		$this->actualizar('salud', $data['salud'], 'id', $salud_id);

		$this->actualizar('extra_curriculares', $data['extra_curriculares'], 'id', $extra_curriculares_id);

	}

	public function listar_estudiantes() {
		
		$stmt = $this->db->query(
          "SELECT i.id AS id_inscripcion, i.fecha_inscripcion,
          e.nombre_1, e.apellido_1, e.nacionalidad, e.cedula_identidad, e.cedula_escolar
          FROM inscripciones i
          INNER JOIN estudiantes e ON i.estudiante_cedula_escolar = e.cedula_escolar
          ORDER BY i.fecha_inscripcion DESC"
        );
        
		return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}


	//=== Metodos para el manejo de datos de representantes ===

	public function guardar_representante($representante, $direccion) {

		$llave_direccion = $this->insertar('direcciones', $direccion);

		return $this->insertar('representantes', [...$representante, 'direccion_id' => $llave_direccion]);
	}

	public function actualizar_representante($representante, $direccion) {

		$cedula = $representante['cedula'];
		
		unset($representante['cedula']);

		$this->actualizar('representantes', $representante, 'cedula', $cedula);

		$direccion_id = $this->buscar_valor('direccion_id', 'representantes', 'cedula', $cedula);

		$this->actualizar('direcciones', $direccion, 'id', $direccion_id);

	}


	//=== Metodos ramdoms que no supe en que categoria meter (pero si sirven) ===

	public function obtener_historial_estudiante ($cedula_identidad, $cedula_escolar) {

		$estudiante = $cedula_identidad ? $this->buscar_todo('estudiantes', 'cedula_identidad', $cedula_identidad) :
		$this->buscar_todo('estudiantes', 'cedula_escolar', $cedula_escolar);

		if(!$estudiante) return null;

		$antropometricos = $this->buscar_todo('antropometricos', 'id', $estudiante['antropometricos_id']);
		$salud = $this->buscar_todo('salud', 'id', $estudiante['salud_id']);
		$extra_curriculares = $this->buscar_todo('extra_curriculares', 'id', $estudiante['extra_curriculares_id']);
		$representante_principal = $this->buscar_todo('representantes', 'cedula', $estudiante['representante_principal_cedula']);
		$direccion_r_principal = $this->buscar_todo('direcciones', 'id', $representante_principal['direccion_id']);

		$representante_secundario = $estudiante['representante_secundario_cedula'] ? $this->buscar_todo('representantes', 'cedula', $estudiante['representante_secundario_cedula']): null;

		$direccion_r_secundario = $representante_secundario ? $this->buscar_todo('direcciones', 'id', $representante_secundario['direccion_id']): null;

		return [
			'estudiante' => $estudiante,
			'antropometricos' => $antropometricos,
			'salud' => $salud,
			'extra_curriculares' => $extra_curriculares,
			'representante_principal' => $representante_principal,
			'representante_secundario' => $representante_secundario,
			'direccion_r_principal' => $direccion_r_principal,
			'direccion_r_secundario' => $direccion_r_secundario
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


	//=== Buscar ===

}

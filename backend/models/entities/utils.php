<?php  

trait Utils {

	public function iniciar_transaccion() { $this->db->beginTransaction(); }

	public function confirmar_transaccion() { $this->db->commit(); }

	public function revertir_transaccion() {
		if ($this->db->inTransaction()) $this->db->rollBack();
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

		return (int )$stmt->rowCount() > 0;
	}


}


?>
<?php 
require_once __DIR__ . '/utils.php';

trait Periodo_academico {
	use Utils;
	
	public function obtener_periodos_academicos($estado_periodo = null) {
        $params = [];
        $sql = "SELECT * FROM PERIODO_ACADEMICO";

        if ($estado_periodo === 'Activo' || $estado_periodo === 'Cerrado'){
            $sql .= "  WHERE estado = :estado";
            $params['estado'] = $estado_periodo; 
        }
        
        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	public function crear_periodo_academico($periodo_academico) {
		$this->cerrar_periodos_activos();
		return $this->insertar('PERIODO_ACADEMICO', ['nombre' => $periodo_academico]);
	}

	public function eliminar_periodo_academico($id) {
		$stmt = $this->db->prepare('DELETE FROM PERIODO_ACADEMICO WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}

	public function alternar_estado_periodo_academico($id) {
		$this->cerrar_periodos_activos($id);
		
		$sql = "
		 UPDATE PERIODO_ACADEMICO SET estado = CASE
			WHEN id = :id AND estado = 'Cerrado' THEN 'Activo'
		 	WHEN id = :id AND estado = 'Activo' THEN 'Cerrado'
		 END
		 WHERE id = :id
		";

		$stmt = $this->db->prepare($sql);
		return $stmt->execute(['id' => $id]);
	}

	private function cerrar_periodos_activos($id = null){
		$sql = "UPDATE PERIODO_ACADEMICO SET estado = 'Cerrado' WHERE estado = 'Activo'";
		$params = [];
		if (!empty($id)){
			$params['id'] = $id;
		 	$sql .= "AND id != :id";
		}

		$stmt = $this->db->prepare($sql);
		return $stmt->execute($params);
	}
}



?>

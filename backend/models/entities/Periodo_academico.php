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
		return $this->insertar('PERIODO_ACADEMICO', ['nombre' => $periodo_academico]);
	}

	public function eliminar_periodo_academico($id) {
		$stmt = $this->db->prepare('DELETE FROM PERIODO_ACADEMICO WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}

	public function alternar_estado_periodo_academico($id) {
		$estado_actual = $this->buscar_valor('estado', 'PERIODO_ACADEMICO', 'id', $id);
		$nuevo_estado = $estado_actual == 'Activo' ? 'Cerrado': 'Activo';
		$stmt = $this->db->prepare("UPDATE PERIODO_ACADEMICO SET estado = :nuevo_estado WHERE id = :id");
		$stmt->execute([
			'nuevo_estado' => $nuevo_estado,
			'id' => $id
		]);
		return true;
	}
}



?>

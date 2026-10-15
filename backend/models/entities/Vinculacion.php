<?php 
require_once __DIR__ . '/utils.php';

trait Vinculacion {
	use Utils;

	public function obtener_vinculaciones() {
        return $this->db->query('
            SELECT gs.id, gs.grado_id, gs.seccion_id, gs.estado, na.id AS nivel_academico_id FROM GRADO_SECCION gs
            JOIN GRADO g ON gs.grado_id = g.id
            JOIN NIVEL_ACADEMICO na ON g.nivel_academico_id = na.id
            ORDER BY gs.id DESC
		')->fetchAll(PDO::FETCH_ASSOC);
    }


	public function crear_vinculacion($grado_id, $seccion_id) {
		return $this->insertar('GRADO_SECCION', [
			'grado_id' => $grado_id,
			'seccion_id' => $seccion_id
		]);
	}

	public function eliminar_vinculacion($id) {
		$stmt = $this->db->prepare('DELETE FROM GRADO_SECCION WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}
	
	public function alternar_estado_vinculacion($id) {
		$estado_actual = $this->buscar_valor('estado', 'GRADO_SECCION', 'id', $id);
		$nuevo_estado = $estado_actual == 'Activo' ? 'Inactivo': 'Activo';
		$stmt = $this->db->prepare("UPDATE GRADO_SECCION SET estado = :nuevo_estado WHERE id = :id");
		$stmt->execute([
			'nuevo_estado' => $nuevo_estado,
			'id' => $id
		]);
		return true;
	}
	
}



?>


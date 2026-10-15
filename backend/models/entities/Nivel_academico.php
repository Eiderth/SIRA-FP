<?php 
require_once __DIR__ . '/utils.php';

trait Nivel_academico {
	use Utils;

	public function obtener_niveles_academicos() {
        return $this->db->query('SELECT * FROM NIVEL_ACADEMICO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    
	public function crear_nivel_academico($nivel_academico) {
		return $this->insertar('NIVEL_ACADEMICO', ['nombre' => $nivel_academico]);
	}

	public function eliminar_nivel_academico($id) {
		$stmt = $this->db->prepare('DELETE FROM NIVEL_ACADEMICO WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}

}

?>

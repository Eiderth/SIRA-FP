<?php 
require_once __DIR__ . '/utils.php';

trait Seccion {
	use Utils;
	
	public function obtener_secciones() {
        return  $this->db->query('SELECT * FROM SECCION ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    
	public function crear_seccion($seccion) {
		return $this->insertar('SECCION', ['nombre' => $seccion]);
	}

	public function eliminar_seccion($id) {
		$stmt = $this->db->prepare('DELETE FROM SECCION WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}

	
}



?>

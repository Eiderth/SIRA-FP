<?php 
require_once __DIR__. '/utils.php';

trait Grado {
	use Utils;

  	public function obtener_grados() {
        return $this->db->query('SELECT * FROM GRADO ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    
	public function crear_grado($grado, $nivel_academico_id) {
		return $this->insertar('GRADO', [
			'nombre' => $grado,
			'nivel_academico_id' => $nivel_academico_id
		]);
	}

	public function eliminar_grado($id) {
		$stmt = $this->db->prepare('DELETE FROM GRADO WHERE id = :id');
		$stmt->execute(['id' => $id]);
		return true;
	}

	
}



?>


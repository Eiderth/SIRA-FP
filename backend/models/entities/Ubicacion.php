<?php 
	
trait Ubicacion {

	public function obtener_paises(){
		return $this->db->query('SELECT * FROM PAIS')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function obtener_estados(){
		return $this->db->query('SELECT * FROM ESTADO')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function obtener_municipios(){
		return $this->db->query('SELECT * FROM MUNICIPIO')->fetchAll(PDO::FETCH_ASSOC);
	}

	public function obtener_parroquias(){
		return $this->db->query('SELECT * FROM PARROQUIA')->fetchAll(PDO::FETCH_ASSOC);	
	}
}

?>
<?php 
foreach(glob(__DIR__ . './../entities/*.php') as $archivo) {
    require_once $archivo;
}

class Busqueda_model{
	use Estudiante,Representante, Grado,Seccion,Periodo_academico;

	protected $db;
	
	function __construct($db){
		$this->db = $db;
	}
}


?>
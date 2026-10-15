<?php

foreach(glob(__DIR__ . './../entities/*.php') as $archivo) {
    require_once $archivo;
}

class Inscripcion_model{
	use Inscripcion, Estudiante, Representante, 
	Grado, Seccion, Nivel_academico, Vinculacion, Periodo_academico,
	Ubicacion;

	protected $db;
	
	function __construct($db){
		$this->db = $db;
	}
}

?>
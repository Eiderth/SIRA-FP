<?php

foreach(glob(__DIR__ . './../entities/*.php') as $archivo) {
    require_once $archivo;
}

class Dashboard_model{
	use Inscripcion, Periodo_academico, Estudiante,Vinculacion;

	protected $db;
	
	function __construct($db){
		$this->db = $db;
	}
}

?>
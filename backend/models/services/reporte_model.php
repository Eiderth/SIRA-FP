<?php

foreach(glob(__DIR__ . './../entities/*.php') as $archivo) {
    require_once $archivo;
}

class Reporte_model{
	use Periodo_academico, Grado, Seccion, Inscripcion, Representante, Ubicacion;

	protected $db;
	
	function __construct($db){
		$this->db = $db;
	}
}

?>
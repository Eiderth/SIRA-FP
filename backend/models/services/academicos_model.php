<?php 
foreach(glob(__DIR__ . '/../entities/*.php') as $archivo) {
    require_once $archivo;
}

class academicos_model{
    use  Grado, Seccion, Nivel_academico, Vinculacion, Periodo_academico;

    protected $db;
    
    function __construct($db){
        $this->db = $db;
    }
}

?>
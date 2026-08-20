<?php

class Dashboard_controller 
{
	private $modelo;

	function __construct($modelo)
	{
		$this->modelo =  $modelo;
	}


	public function init($action,$input){
		switch ($action) {
			case 'obtener_datos_resumen':
				$this->obtener_datos_resumen();
				break;
			
			default:
				// code...
				break;
		}
	}

	private function obtener_datos_resumen(){
		$periodo = $this->modelo->buscar_valor('nombre', 'PERIODO_ACADEMICO', 'estado', 'Activo');
		$estudiantes_activos = $this->modelo->buscar_valor('count(*)', 'PERSONA_ESTUDIANTE', 'estado', 'Activo');
		$grados_secciones_activas = $this->modelo->buscar_valor('count(*)', 'GRADO_SECCION', 'estado', 'Activo');
		$resumen_reportes = $this->modelo->listar_estudiantes(20);

		echo json_encode([
			'estado' => 'completado', 
			'data' => [
				'periodo_activo' => $periodo,
				'estudiantes_activos' => $estudiantes_activos,
				'grados_secciones_activas' => $grados_secciones_activas,
				'resumen_reportes' => $resumen_reportes
			]
		]);
	}
}

?>
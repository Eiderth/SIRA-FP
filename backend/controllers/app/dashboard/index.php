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

		$ingresos_mensuales = $this->modelo->obtener_ingresos_mensuales_representantes();
		$promedio_ingresos = array_sum(array_column($ingresos_mensuales, 'ingreso_mensual')) /count($ingresos_mensuales);
		$periodo_id = $this->modelo->buscar_valor('id', 'PERIODO_ACADEMICO', 'estado', 'Activo');

		echo json_encode([
			'estado' => 'completado', 
			
			'periodo_activo' => ($this->modelo->buscar_valor('nombre', 'PERIODO_ACADEMICO', 'estado', 'Activo')?: "no hay periodo activo"),
			'estudiantes_activos' => $this->modelo->buscar_valor('count(*)', 'PERSONA_ESTUDIANTE', 'estado', 'Activo'),
			'grados_secciones_activas' => $this->modelo->buscar_valor('count(*)', 'GRADO_SECCION', 'estado', 'Activo'),
			
			'promedio_ingresos' => $promedio_ingresos,
			'top_profesiones' => $this->modelo->obtener_top_profesiones(),
			
			'rangos_ingresos' => $this->modelo->obtener_rangos_ingresos(),
			'estudiantes_por_seccion' => $this->modelo->obtener_inscripciones_por_grado_seccion($periodo_id), 
			
			'salud' => $this->modelo->obtener_resumen_salud($periodo_id),
		    'promedios_antropometricos' => $this->modelo->obtener_promedios_antropometricos($periodo_id),
		    'deportes' => $this->modelo->obtener_resumen_deportes($periodo_id),
		    'canaimas' => $this->modelo->obtener_resumen_canaimas($periodo_id),

		    'top_sectores' => $this->modelo->obtener_top_sectores($periodo_id),
		    'tipos_vivienda' => $this->modelo->obtener_tipos_vivienda($periodo_id),
		    'condiciones_vivienda' => $this->modelo->obtener_condiciones_vivienda($periodo_id),

			'resumen_reportes' => $this->modelo->listar_inscripciones(20),
		]);
	}
}

?>
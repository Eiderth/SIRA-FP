<?php 

class Grados_secciones_controller
{
	private $modelo;
	private $sistema;
	function __construct($modelo, $sistema)
	{
		$this->modelo = $modelo;
		$this->sistema = $sistema;
	}

	public function init($action, $input){

		switch ($action) {

			case 'obtener_niveles_academicos': 
				$this->obtener_niveles_academicos();
				break;
			case 'crear_nivel_academico':
				$this->crear_nivel_academico($input);
				break;
			case 'eliminar_nivel_academico': 
				$this->eliminar_nivel_academico($input);
				break;

			case 'obtener_grados': 
				$this->obtener_grados();
				break;	
			case 'crear_grado':
				$this->crear_grado($input);
				break;
			case 'eliminar_grado': 
				$this->eliminar_grado($input);
				break;

			case 'obtener_secciones': 
				$this->obtener_secciones();
				break;	
			case 'crear_seccion':
				$this->crear_seccion($input);
				break;
			case 'eliminar_seccion': 
				$this->eliminar_seccion($input);
				break;

			case 'obtener_grados_secciones':
				$this->obtener_grados_secciones();
				break;
			case 'crear_grado_seccion':
				$this->crear_grado_seccion($input);
				break;
			case 'eliminar_grado_seccion': 
				$this->eliminar_grado_seccion($input);
				break;
			case 'alternar_estado_grado_seccion': 
				$this->alternar_estado_grado_seccion($input);
				break;

			default:
				http_response_code(404);
	            echo json_encode(['estado' => 'error', 'mensaje' => 'acción no válida']);
				break;
		}
	}

	// === NIVELES ACADEMICOS ===

	private function obtener_niveles_academicos(){
		$niveles = $this->sistema->obtener_niveles_academicos();
		
		if (empty($niveles)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'error al traer niveles academicos']);
			exit();
		}

		echo json_encode(['estado' => 'completado', 'niveles_academicos' => $niveles]);
	}

	private function crear_nivel_academico($input){

		if ($this->modelo->existe('NIVEL_ACADEMICO', 'nombre', $input['nombre'])) {
			echo json_encode(['estado' => 'error', 'mensaje' => 'Ya existe este nivel academico']);
			exit();
		} 

		$nivel_id = $this->modelo->crear_nivel_academico($input);
		
		if (empty($nivel_id)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al guardar']);
			exit();
		}
		echo json_encode(['estado' => 'completado', 'nivel_academico_id' => $nivel_id]);
		 
	}

	private function eliminar_nivel_academico($input){

		if ($this->modelo->existe('GRADO', 'nivel_academico_id', $input['id'])) {
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'Verifique que ningun grado dependa de este nivel'
			]);
			exit();
		} 

		$eliminado = $this->modelo->eliminar_nivel_academico($input['id']);
		
		if (empty($eliminado)){
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'algo ha salido mal intente de nuevo'
			]);
			exit();
		}
		echo json_encode(['estado' => 'completado', 'mensaje' => 'Nivel academico eliminado con exito']);
	}

	// === GRADOS ===

	private function obtener_grados(){
		$grado_niveles = $this->sistema->obtener_grados();
		
		if (empty($grado_niveles)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al traer grados academicos']);
			exit();
		}
		echo json_encode(['estado' => 'completado', 'data' => $grado_niveles]);

	}

	private function crear_grado($input){
		if ($this->modelo->existe('GRADO', 'nombre', $input['nombre'])) {
			echo json_encode(['estado' => 'error', 'mensaje' => 'Ese grado ya existe']); 
			exit(); 	
		} 

		$grado_id = $this->modelo->crear_grado($input);
		
		if (empty($grado_id)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al guardar']); 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'grado_id' => $grado_id]);
	}


	private function eliminar_grado($input){

		if ($this->modelo->existe('GRADO_SECCION', 'grado_id', $input['id'])) {
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'Revise que ninguna seccion dependa de este grado e intente de nuevo'
			]);
			exit();	
		} 

		$eliminado = $this->modelo->eliminar_grado($input['id']);
		
		if (empty($eliminado)){
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'algo ha salido mal'
			]);		 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'mensaje' => 'grado eliminado con exito']);
	}


	//=== SECCIONES ===


	private function obtener_secciones(){
		$secciones = $this->sistema->obtener_secciones();
		
		if (empty($secciones)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al traer secciones']);
			exit();
		}
		echo json_encode(['estado' => 'completado', 'secciones' => $secciones]);

	}

	private function crear_seccion($input){
		if ($this->modelo->existe('SECCION', 'nombre', $input['nombre'])) {
			echo json_encode(['estado' => 'error', 'mensaje' => 'Esta seccion ya existe']); 
			exit(); 	
		} 

		$seccion_id = $this->modelo->crear_seccion($input);
		
		if (empty($seccion_id)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al guardar']); 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'seccion_id' => $seccion_id]);
	}


	private function eliminar_seccion($input){

		if ($this->modelo->existe('GRADO_SECCION', 'seccion_id', $input['id'])) {
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'Revise que ninguna vinculacion dependa de esta seccion e intente de nuevo'
			]);
			exit();	
		} 

		$eliminado = $this->modelo->eliminar_seccion($input['id']);
		
		if (empty($eliminado)){
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'algo ha salido mal'
			]);		 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'mensaje' => 'seccion eliminada con exito']);
	}

	// === VINCULAR GRADOS-SECCIONES ===

	private function obtener_grados_secciones(){
		$grados_secciones = $this->sistema->obtener_grados_secciones();
		
		if (empty($grados_secciones)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al traer grados ysecciones']);
			exit();
		}
		echo json_encode(['estado' => 'completado', 'data' => $grados_secciones]);

	}

	private function crear_grado_seccion($input){
		if ($this->modelo->existe('GRADO_SECCION', 'grado_id, seccion_id', [$input['grado_id'], $input['seccion_id']]) ) {
			echo json_encode(['estado' => 'error', 'mensaje' => 'Esta vinculacion ya existe']); 
			exit(); 	
		} 

		$grado_seccion_id = $this->modelo->crear_grado_seccion($input);
		
		if (empty($grado_seccion_id)){
			echo json_encode(['estado' => 'error', 'mensaje' => 'Error al guardar']); 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'grado_seccion_id' => $grado_seccion_id]);
	}


	private function eliminar_grado_seccion($input){
		if ($this->modelo->existe('INSCRIPCION', 'grado_seccion_id', $input['id'])) {
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'no se puede eliminar esta vinculacion porque existen inscripciones vinculadas a ella, desactivela si no la quiere usar'
			]);
			exit();	
		} 

		$eliminado = $this->modelo->eliminar_grado_seccion($input['id']);
		
		if (empty($eliminado)){
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'algo ha salido mal'
			]);		 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'mensaje' => 'vinculacion eliminada con exito']);
	}

	private function alternar_estado_grado_seccion($input){

		$alternado = $this->modelo->alternar_estado_grado_seccion($input['id']);
		
		if (empty($alternado)){
			echo json_encode([
				'estado' => 'error',
				'mensaje' => 'algo ha salido mal'
			]);		 
			exit();
		}
		echo json_encode(['estado' => 'completado', 'mensaje' => 'estado cambiado']);
	}



}

?>
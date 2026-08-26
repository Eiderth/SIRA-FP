import Utils from "../../../core/utils.js";
import Periodos_academicos_controlller from "./secciones/periodos_academicos.js";
import Vincular_grados_secciones_controller from "./secciones/vincular.js";
import Grados_controlller from "./secciones/grados.js";
import Secciones_controlller from "./secciones/secciones.js";
import Niveles_academicos_controlller from "./secciones/niveles_academicos.js";

export default class Grados_secciones_controller extends Utils  {

	#html;

	#seccion;
	#botones = {};
	#periodos_academicos_controller;
	#niveles_academicos_controller;
	#grados_controller;
	#secciones_controller;
	#vincular_grados_secciones_controller;



	constructor() {
		super();
		this.#html = this._traer_html("./frontend/views/app/grados-secciones/index.html");
	}

	async init(seccion = '') {
		const html = await this.#html;
		await this._inyectar_html(document.getElementById('root-app'), html);
		this.#seccion = null;

		localStorage.removeItem('parametros_formulario')
		this.#periodos_academicos_controller = new Periodos_academicos_controlller();
		this.#vincular_grados_secciones_controller = new Vincular_grados_secciones_controller();
		this.#grados_controller = new Grados_controlller();
		this.#secciones_controller = new Secciones_controlller();
		this.#niveles_academicos_controller = new Niveles_academicos_controlller();

		this.#botones.periodos_academicos = document.getElementById('tab-gestion-periodos');
		this.#botones.vincular = document.getElementById('tab-vincular-aula');
		this.#botones.grados = document.getElementById('tab-gestion-grados');
		this.#botones.secciones = document.getElementById('tab-gestion-secciones');
		this.#botones.niveles_academicos = document.getElementById('tab-gestion-niveles');

		this.#dar_eventos_click();

		switch (seccion){

			case 'periodos_academicos':
				this.#botones.periodos_academicos.click();
				break;

			case 'vinculaciones':
				this.#botones.vincular.click(); 
				break;

			case 'grados':
				this.#botones.grados.click();  
				break;

			case 'secciones':
				this.#botones.secciones.click();  
				break;
		
			case 'niveles_academicos':
				this.#botones.niveles_academicos.click();
				break;

			default: 
				this.#botones.periodos_academicos.click();
				break;
	
		}
	}

	#dar_eventos_click() {
		this.#botones.periodos_academicos.addEventListener('click', () => {
			if (this.#seccion == 'periodos_academicos') return;
			this.#periodos_academicos_controller.init();
			this.#seccion = 'periodos_academicos';
		})
		this.#botones.niveles_academicos.addEventListener('click', () => {
			if (this.#seccion == 'niveles_academicos') return;
			this.#niveles_academicos_controller.init();
			this.#seccion = 'niveles_academicos';
		})
		this.#botones.grados.addEventListener('click', () => {
			if (this.#seccion == 'grados') return;
			this.#grados_controller.init();
			this.#seccion = 'grados';
		})
		this.#botones.secciones.addEventListener('click', () => {
			if (this.#seccion == 'secciones') return;
			this.#secciones_controller.init();
			this.#seccion = 'secciones';
		})
		this.#botones.vincular.addEventListener('click', () => {
			if (this.#seccion == 'vincular') return;
			this.#vincular_grados_secciones_controller.init();
			this.#seccion = 'vincular';
		})
	
	}

}
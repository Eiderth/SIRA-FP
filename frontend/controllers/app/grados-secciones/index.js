import Utils from "../../../core/utils.js";
import Niveles_academicos_controlller from "./secciones/niveles_academicos.js";
import Grados_controlller from "./secciones/grados.js";
import Secciones_controlller from "./secciones/secciones.js";
import Vincular_grados_secciones_controller from "./secciones/vincular.js";

export default class Grados_secciones_controller extends Utils  {

	#html;

	#seccion;
	#botones = {};
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


		this.#niveles_academicos_controller = new Niveles_academicos_controlller();
		this.#grados_controller = new Grados_controlller();
		this.#secciones_controller = new Secciones_controlller();
		this.#vincular_grados_secciones_controller = new Vincular_grados_secciones_controller();

		this.#botones.vincular = document.getElementById('tab-vincular-aula');
		this.#botones.niveles_academicos = document.getElementById('tab-gestion-niveles');
		this.#botones.grados = document.getElementById('tab-gestion-grados');
		this.#botones.secciones = document.getElementById('tab-gestion-secciones');

		this.#dar_eventos_click();

		switch (seccion){
			case 'niveles_academicos':
				this.#botones.niveles_academicos.click();
				break;
	
			case 'grados':
				this.#botones.grados.click();  
				break;

			case 'secciones':
				this.#botones.secciones.click();  
				break;
			case 'vinculaciones':
				this.#botones.vincular.click(); 
				break;
		}
	}

	#dar_eventos_click() {
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










// async #rellenar_tabla_gs() { 
	// 	const resp = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_grados_secciones');
	// 	const data = resp.data;
	// 	let tbody_g = ''; 
	// 	let tbody_gs = ''; 
	// 	let tbody_s = ''; 

	// 	data.grados.forEach(g => {
	// 		tbody_g += `
	// 			<tr class="border-bottom">
    //                 <td class="ps-3 py-2 text-dark fs-7 fw-bold">${g.nombre}</td>
    //                 <td class="py-2 fw-bold text-dark">${data.niveles_academicos.find(na => na.id == g.nivel_academico_id)?.nombre}</td>
    //                 <td class="text-end pe-3 py-2">
    //                     <button class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" data-id=${g.id} title="Eliminar"><i class="bi bi-trash-fill fs-7"></i></button>
    //                 </td>
    //             </tr>
	// 		`
	// 		tbody_gs += `
	//            <tr class="border-bottom">
	//                 <td class="ps-3 py-2 fw-bold text-dark">
	// 	                ${g.nombre}
	//                 </td>
	//                 <td class="py-2">
	//                 	<span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7 px-2.5 py-1 rounded-pill">
	// 						${(() => {
	// 							let str = '';
	// 							data.grados_secciones.filter(gs => gs.grado_id == g.id)?.forEach(gs => {
	// 								str += ' ' + data.secciones.find(s => s.id == gs.seccion_id)?.nombre;
	// 							})
	// 							return str;
	// 						})()}
	// 	                </span>
	//                 </td>
	//                 <td class="py-2 text-dark fs-7 fw-bold"> 
	// 					${data.niveles_academicos.find(na => na.id == g.nivel_academico_id)?.nombre}
	//                 </td>
	//                 <td class="text-end pe-3 py-2">
	//                 	<button class="btn btn-outline-primary btn-sm py-0 px-2 me-1 rounded-2" title="Editar">
	//                 		<i class="bi bi-pencil-fill fs-7"></i>
	//                 	</button>
	//                     <button class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" data-id=${g.id} title="Elimina toda la vinculacion">
	//                         <i class="bi bi-trash-fill fs-7"></i>
	//                     </button>
	//                 </td>
	//             </tr>
	// 		`
	// 	});

	// 	data.secciones.forEach(s => {
	// 		tbody_s += `
	// 	       <tr class="border-bottom">
	//                 <td class="ps-3 py-2 fw-bold text-dark">${s.nombre}</td>
	//                 <td class="text-end pe-3 py-2">
	//                     <button class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" data-id=${s.id} title="Eliminar"><i class="bi bi-trash-fill fs-7"></i></button>
	//                 </td>
	//             </tr>
	// 		`
	// 	});
	// 	document.getElementById('tbody-grados').innerHTML = tbody_g;
	// 	document.getElementById('tbody-grados-secciones').innerHTML = tbody_gs;
	// 	document.getElementById('tbody-secciones').innerHTML = tbody_s;
	// }




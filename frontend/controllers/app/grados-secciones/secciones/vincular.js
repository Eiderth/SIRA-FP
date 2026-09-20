import Utils from "../../../../core/utils.js";


export default class Vincular_grados_secciones_controller extends Utils {
	
	#form;
	#tbody;
	#select_grado;
	#select_seccion;

	#menu_vinculacion;
	#btn_crear_grado_seccion;
	#btn_eliminar_vinculacion;
	#btn_alternar_estado_vinculacion
	#vinculacion_id = null;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-grados-secciones');
		this.#btn_crear_grado_seccion = document.getElementById('btn-crear-grado-seccion');
		this.#form = document.getElementById('form-crear-grado-seccion');
		this.#select_grado = document.getElementById('select-grado');
		this.#select_seccion = document.getElementById('select-seccion');

		this.#menu_vinculacion = document.getElementById('menu-vinculacion');
		this.#btn_eliminar_vinculacion = document.getElementById('btn-eliminar-vinculacion');
		this.#btn_alternar_estado_vinculacion = document.getElementById('btn-alternar-estado-vinculacion');

		this.#dar_evento_dom();
		this.#btn_eliminar_vinculacion.addEventListener('click', () => this.#eliminar_vinculacion());
		this.#btn_alternar_estado_vinculacion.addEventListener('click', () => this.#alternar_estado_vinculacion());
		this.#btn_crear_grado_seccion.addEventListener('click', () => this.#crear_grado_seccion());
	}

	async init () {
		await this.#llenar_tabla();
	}

	#dar_evento_dom() {

		document.addEventListener('click', () => {
			this.#menu_vinculacion.style.display = 'none';
			this.#vinculacion_id = null;
		})
	}

	async #eliminar_vinculacion() {	
		if (this.#vinculacion_id) {
			await this.#eliminar_grado_seccion(this.#vinculacion_id);
			this.#menu_vinculacion.style.display = 'none';
		}
	}

	async #alternar_estado_vinculacion() {
		if (this.#vinculacion_id) {
			await this.#alternar_estado_grado_seccion(this.#vinculacion_id);
			this.#menu_vinculacion.style.display = 'none';
		}
	}

	async #crear_grado_seccion(){

		if (!this._validar_formulario(this.#form)) {
			this._notificacion('debe llenar el formulario', 'Error');
			return;
		}
		
     	const data = Object.fromEntries(new FormData(this.#form));
     	this._limpiar_objeto(data);

     	if(data.grado_id === '' || parseInt(data.grado_id) < 1 || data.seccion_id === '' || parseInt(data.seccion_id) < 1){
            this._notificacion('debe llenar todos los campos', 'Error');
            return;
	    }

		this.#btn_crear_grado_seccion.disabled = true;
    
     	const {creado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_grado_seccion', data);

		if (creado) {
			this.#llenar_tabla();
		}
		
		this.#btn_crear_grado_seccion.disabled = false;

	}

	async #llenar_tabla() {
		const {
			grados_secciones, 
			niveles_academicos, 
			grados, 
			secciones
		} = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_grados_secciones');
		
		if (grados_secciones && niveles_academicos && grados && secciones) {
			
			let tbody = '';
			
			grados_secciones.reverse();
			
			grados.forEach(g => {
				tbody += `
		           <tr class="border-bottom">
		                <td class="ps-3 py-2 fw-bold text-dark">
			                ${g.nombre}
		                </td>
					    <td class="py-2 text-dark fs-7 fw-bold"> 
							${niveles_academicos.find(na => na.id == g.nivel_academico_id)?.nombre}
		                </td>
		                <td class="text-end pe-3 py-2">
							${
		    					grados_secciones
						        .filter(gs => gs.grado_id == g.id)
						        .map(gs => {
						        	const secNombre = secciones.find(s => s.id == gs.seccion_id)?.nombre;
						            return `<span title="${gs.estado}"  data-id="${gs.id}" class="badge ${gs.estado !== 'Activo'? 'bg-light opacity-50 text-muted': 'bg-primary-subtle text-primary'} border border-primary-subtle small px-2 py-1 rounded-pill badge-vinculacion user-select-none" style="cursor: pointer;">${secNombre}<i class="bi bi-three-dots-vertical ms-1 small"></i></span>`;
						        }).join(' ') 
						    }
		                </td>
		            </tr>
				`
			});
			this.#tbody.innerHTML = tbody;
			this._llenar_select(this.#select_grado, grados, '');
			this._llenar_select(this.#select_seccion, secciones, '');

			document.querySelectorAll('.badge-vinculacion').forEach(badge => {
				badge.addEventListener('click', (e) => {
					e.preventDefault();
					e.stopPropagation();

					const activo = e.currentTarget.getAttribute('title') == 'Activo';
					document.getElementById('texto-btn-alternar-vinculacion').textContent = `${activo ? 'Desactivar aula' : 'Activar aula'}`;

					this.#vinculacion_id = e.currentTarget.getAttribute('data-id');
					this.#menu_vinculacion.style.display = 'block';
					const left = (e.clientX + this.#menu_vinculacion.offsetWidth)> window.innerWidth ? window.innerWidth - this.#menu_vinculacion.offsetWidth - 20 : e.clientX;
					const top = (e.clientY + this.#menu_vinculacion.offsetHeight) > window.innerHeight ? window.innerHeight - this.#menu_vinculacion.offsetHeight - 20 : e.clientY;
					this.#menu_vinculacion.style.left = `${left}px`;
					this.#menu_vinculacion.style.top = `${top}px`;

				})
			})
		}

	}

	async #eliminar_grado_seccion(id) {
		const {eliminado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_grado_seccion', {'id': id}); 
		if (eliminado) {
			this.#llenar_tabla();
		} 
	}
	async #alternar_estado_grado_seccion(id) {
		const {alternado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=alternar_estado_grado_seccion', {'id': id}); 
		if (alternado) {
			this.#llenar_tabla();
		} 
	}
}
import Utils from "../../../../core/utils.js";


export default class vinculaciones_controller extends Utils {
	
	#form;
	#tbody;
	#select_grado;
	#select_seccion;

	#menu_vinculacion;
	#btn_crear_vinculacion;
	#btn_eliminar_vinculacion;
	#btn_alternar_estado_vinculacion
	#vinculacion_id = null;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-grados-secciones');
		this.#btn_crear_vinculacion = document.getElementById('btn-crear-grado-seccion');
		this.#form = document.getElementById('form-crear-grado-seccion');
		this.#select_grado = document.getElementById('select-grado');
		this.#select_seccion = document.getElementById('select-seccion');

		this.#menu_vinculacion = document.getElementById('menu-vinculacion');
		this.#btn_eliminar_vinculacion = document.getElementById('btn-eliminar-vinculacion');
		this.#btn_alternar_estado_vinculacion = document.getElementById('btn-alternar-estado-vinculacion');

		this.#dar_evento_dom();
		this.#btn_eliminar_vinculacion.addEventListener('click', async () => {
			if (!this.#vinculacion_id) return;
			await this.#eliminar_vinculacion(this.#vinculacion_id);
			this.#menu_vinculacion.style.display = 'none';
		});

		this.#btn_alternar_estado_vinculacion.addEventListener('click', async() => {
			if (!this.#vinculacion_id) return; 
			await this.#alternar_estado_vinculacion(this.#vinculacion_id);
			this.#menu_vinculacion.style.display = 'none';
		});

		this.#btn_crear_vinculacion.addEventListener('click', () => this.#crear_vinculacion());
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

	async #crear_vinculacion(){

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

		this.#btn_crear_vinculacion.disabled = true;
    
     	const {creado} = await this._enviar_datos('./api.php?controller=academicos_controller&action=crear_vinculacion', data);

		if (creado) {
			this.#llenar_tabla();
		}
		
		this.#btn_crear_vinculacion.disabled = false;

	}

	async #llenar_tabla() {
		const {
			vinculaciones, 
			niveles_academicos, 
			grados, 
			secciones
		} = await this._traer_datos('./api.php?controller=academicos_controller&action=obtener_vinculaciones');
		
		if (vinculaciones && niveles_academicos && grados && secciones) {
			
			let tbody = '';
			
			vinculaciones.reverse();
			
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
		    					vinculaciones
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

	async #eliminar_vinculacion(id) {
		const {eliminado} = await this._enviar_datos('./api.php?controller=academicos_controller&action=eliminar_vinculacion', {'id': id}); 
		if (eliminado) {
			this.#llenar_tabla();
		} 
	}
	async #alternar_estado_vinculacion(id) {
		const {alternado} = await this._enviar_datos('./api.php?controller=academicos_controller&action=alternar_estado_vinculacion', {'id': id}); 
		if (alternado) {
			this.#llenar_tabla();
		} 
	}
}
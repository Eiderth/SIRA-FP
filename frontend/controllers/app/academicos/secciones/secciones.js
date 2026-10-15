import Utils from "../../../../core/utils.js";


export default class Secciones_controller extends Utils {
	
	#form;
	#btn_crear_seccion;
	#tbody;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-secciones');
		this.#btn_crear_seccion = document.getElementById('btn-crear-seccion');
		this.#form = document.getElementById('form-crear-seccion');
		this.#btn_crear_seccion.addEventListener('click',() => this.#crear_seccion());
	}

	async init () {
		await this.#llenar_tabla();
	}

	async #crear_seccion() {

		if (!this._validar_formulario(this.#form)) {
			this._notificacion('debe llenar el formulario', 'Error');
			return;
		}

     	const data = Object.fromEntries(new FormData(this.#form));
     	this._limpiar_objeto(data);

		if(data.nombre === ''){
            this._notificacion('debe llenar todos los campos', 'Error');
            return;
	    }

		this.#btn_crear_seccion.disabled = true;
     	const {creada} = await this._enviar_datos('./api.php?controller=academicos_controller&action=crear_seccion', data);

		if (creada) {
			this.#llenar_tabla();
			this.#form.reset();
		} 
		
		this.#btn_crear_seccion.disabled = false;
		
	}

	async #llenar_tabla() {
		const {secciones} = await this._traer_datos('./api.php?controller=academicos_controller&action=obtener_secciones');
		
		if (secciones) {

			let tbody = '';
			secciones.forEach(s => {
				tbody += `
					<tr class="border-bottom">
		                <td class="ps-3 py-2 fw-bold text-dark">${s.nombre}</td>
		                <td class="text-end pe-3 py-2">
		                    <button type="button" data-id=${s.id} title="Eliminar" class="btn-eliminar-seccion btn btn-outline-danger btn-sm py-0 px-2 rounded-2"><i class="bi bi-trash-fill fs-7"></i></button>
		                </td>
		            </tr>
				`
			});
		
			this.#tbody.innerHTML = tbody;
			document.querySelectorAll('.btn-eliminar-seccion').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const id = e.currentTarget.getAttribute('data-id');
					this.#eliminar_seccion(id);
				})
			});
		
		}

	}

	async #eliminar_seccion(id) {
		const {eliminada} = await this._enviar_datos('./api.php?controller=academicos_controller&action=eliminar_seccion', {'id': id}); 
		if (eliminada) {
			this.#llenar_tabla();
		} 

	}

}
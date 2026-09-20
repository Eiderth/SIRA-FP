import Utils from "../../../../core/utils.js";


export default class Niveles_academicos_controller extends Utils {
	
	#form;
	#btn_crear_nivel_academico;
	#tbody;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-niveles-academicos');
		this.#btn_crear_nivel_academico = document.getElementById('btn-crear-nivel-academico');
		this.#form = document.getElementById('form-crear-nivel-academico');
		this.#form.addEventListener('click', () => this.#crear_nivel_academico());
	}

	async init () {
		await this.#llenar_tabla();
	}

	async #crear_nivel_academico(){

		if (!this._validar_formulario(this.#form)) {
			this._notificacion('debe llenar el formulario', 'Error');
			return;
		}

     	const data = Object.fromEntries(new FormData(this.#form));
     	this._limpiar_objeto(data);

     	if (data.nombre === '') {
     		this._notificacion('Debe llenar todos los campos', 'Error');
     		return;
     	}

		this.#btn_crear_nivel_academico.disabled = true;
     	const {creado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_nivel_academico', data);

		if (creado) {
			this.#llenar_tabla();
			this.#form.reset();
		}

		this.#btn_crear_nivel_academico.disabled = false;

	}

	async #llenar_tabla() {
		const {niveles_academicos} = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_niveles_academicos');

		if (niveles_academicos) {

			let tbody = '';
			niveles_academicos.forEach(na => {
				tbody += `
					<tr class="border-bottom">
		                <td class="ps-3 py-2 fw-bold text-dark">${na.nombre}</td>
		                <td class="text-end pe-3 py-2">
		                    <button type="button" data-id="${na.id}" title="Eliminar" class="btn-eliminar-nivel btn btn-outline-danger btn-sm py-0 px-2 rounded-2" ><i class="bi bi-trash-fill fs-7"></i></button>
		                </td>
		            </tr>
				`
			});

			this.#tbody.innerHTML = tbody;
			document.querySelectorAll('.btn-eliminar-nivel').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const id = e.currentTarget.getAttribute('data-id');
					this.#eliminar_nivel_academico(id);
				})
			});

		}

	}

	async #eliminar_nivel_academico(id) {
		const {eliminado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_nivel_academico', {'id': id}); 
		if (eliminado) {
			this.#llenar_tabla();
		} 

	}
}
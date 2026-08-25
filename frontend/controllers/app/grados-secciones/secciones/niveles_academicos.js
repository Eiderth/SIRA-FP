import Utils from "../../../../core/utils.js";


export default class Niveles_academicos_controller extends Utils {
	#form;
	#btn;
	#tbody;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-niveles-academicos');
		this.#btn = document.getElementById('btn-crear-nivel-academico');
		this.#form = document.getElementById('form-crear-nivel-academico');
		this.#dar_eventos_formularios();
	}

	async init () {
		await this.#llenar_tabla();
	}

	async #dar_eventos_formularios(){

		this.#btn.addEventListener('click', async () => {
			if (!this._validar_formulario(this.#form)) {
				this._notificacion('debe llenar el formulario');
				return;
			}

			this.#btn.disabled = true;
         	const data = Object.fromEntries(new FormData(this.#form));
         	this._limpiar_objeto(data);

         	const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_nivel_academico', data);

			if (resp.estado == 'completado') {
				this.#llenar_tabla();
				this.#form.reset();
			} else {
				this._notificacion(resp.mensaje);
			}

			setTimeout(()=>{
				this.#btn.disabled = false;
			}, 500)

		});
	}

	async #llenar_tabla() {
		const resp = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_niveles_academicos');
		let tbody = '';
		resp.niveles_academicos.forEach(na => {
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

	async #eliminar_nivel_academico(id) {
		const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_nivel_academico', {'id': id}); 
		if (resp.estado != 'completado') {
			this._notificacion(resp.mensaje);
			return;
		} 
		this.#llenar_tabla();

	}
}
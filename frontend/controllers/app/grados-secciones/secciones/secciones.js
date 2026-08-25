import Utils from "../../../../core/utils.js";


export default class Secciones_controller extends Utils {
	
	#form;
	#btn;
	#tbody;
	#select;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-secciones');
		this.#btn = document.getElementById('btn-crear-seccion');
		this.#form = document.getElementById('form-crear-seccion');
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
        
         	const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_seccion', data);

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
		const resp = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_secciones');
		let tbody = '';
		resp.secciones.forEach(s => {
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

	async #eliminar_seccion(id) {
		const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_seccion', {'id': id}); 
		if (resp.estado != 'completado') {
			this._notificacion(resp.mensaje);
			return;
		} 
		this.#llenar_tabla();

	}

}
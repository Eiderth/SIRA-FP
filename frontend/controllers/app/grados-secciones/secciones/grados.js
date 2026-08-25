import Utils from "../../../../core/utils.js";


export default class Grados_controller extends Utils {
	
	#form;
	#btn;
	#tbody;
	#select;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-grados');
		this.#btn = document.getElementById('btn-crear-grado');
		this.#form = document.getElementById('form-crear-grado');
		this.#select = document.getElementById('select-nivel-academico');
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
        
         	const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_grado', data);

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
		const resp = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_grados');
		let tbody = '';
		resp.data.grados.forEach(g => {
			tbody += `
				<tr class="border-bottom">
                    <td class="ps-3 py-2 text-dark fs-7 fw-bold">${g.nombre}</td>
                    <td class="py-2 fw-bold text-dark">${resp.data.niveles_academicos.find(na => na.id == g.nivel_academico_id)?.nombre}</td>
                    <td class="text-end pe-3 py-2">
                        <button type="button" data-id=${g.id} title="Eliminar" class="btn-eliminar-grado btn btn-outline-danger btn-sm py-0 px-2 rounded-2"><i class="bi bi-trash-fill fs-7"></i></button>
                    </td>
                </tr>
			`
		});
		this._llenar_select(this.#select, resp.data.niveles_academicos, '');
		this.#tbody.innerHTML = tbody;
		document.querySelectorAll('.btn-eliminar-grado').forEach(btn => {
			btn.addEventListener('click', (e) => {
				const id = e.currentTarget.getAttribute('data-id');
				this.#eliminar_grado(id);
			})
		});

	}

	async #eliminar_grado(id) {
		const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_grado', {'id': id}); 
		if (resp.estado != 'completado') {
			this._notificacion(resp.mensaje);
			return;
		} 
		this.#llenar_tabla();

	}
}
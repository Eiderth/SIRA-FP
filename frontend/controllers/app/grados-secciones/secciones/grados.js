import Utils from "../../../../core/utils.js";


export default class Grados_controller extends Utils {
	
	#form;
	#btn_crear_grado;
	#tbody;
	#select;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-grados');
		this.#btn_crear_grado = document.getElementById('btn-crear-grado');
		this.#form = document.getElementById('form-crear-grado');
		this.#select = document.getElementById('select-nivel-academico');
		this.#dar_eventos_formularios();
	}

	async init () {
		await this.#llenar_tabla();
	}

	async #dar_eventos_formularios(){

		this.#btn_crear_grado.addEventListener('click', async () => {

			if (!this._validar_formulario(this.#form)) {
				this._notificacion('debe llenar el formulario', 'Error');
				return;
			}
			
         	const data = Object.fromEntries(new FormData(this.#form));
         	this._limpiar_objeto(data);

	        if(data.nombre === '' || data.nivel_academico_id === '' || parseInt(data.nivel_academico_id) < 1){
	            this._notificacion('debe llenar todos los campos', 'Error');
	            return;
	        }
        
			this.#btn_crear_grado.disabled = true;
         	const { creado } = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_grado', data);

			if ( creado ) {
			
				this.#llenar_tabla();
				this.#form.reset();
			
			} 
			
			this.#btn_crear_grado.disabled = false;

		});
	}

	async #llenar_tabla() {
		const {grados, niveles_academicos} = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_grados_niveles_academicos');
		
		if (grados && niveles_academicos) {
			
			let tbody = '';
			grados.forEach(g => {
				tbody += `
					<tr class="border-bottom">
	                    <td class="ps-3 py-2 text-dark fs-7 fw-bold">${g.nombre}</td>
	                    <td class="py-2 fw-bold text-dark">${niveles_academicos.find(na => na.id == g.nivel_academico_id)?.nombre}</td>
	                    <td class="text-end pe-3 py-2">
	                        <button type="button" data-id=${g.id} title="Eliminar" class="btn-eliminar-grado btn btn-outline-danger btn-sm py-0 px-2 rounded-2"><i class="bi bi-trash-fill fs-7"></i></button>
	                    </td>
	                </tr>
				`
			});

			this._llenar_select(this.#select, niveles_academicos, '');
			this.#tbody.innerHTML = tbody;

			document.querySelectorAll('.btn-eliminar-grado').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const id = e.currentTarget.getAttribute('data-id');
					this.#eliminar_grado(id);
				})
			});

		}

	}

	async #eliminar_grado(id) {
		const {eliminado} = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_grado', {'id': id}); 
		if (eliminado) {
			this.#llenar_tabla();
		} 

	}
}
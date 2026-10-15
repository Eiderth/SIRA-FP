import Utils from "../../../../core/utils.js";


export default class Periodos_academicos_controller extends Utils {
	#form;
	#btn_crear_periodo_academico;
	#tbody;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-periodos-academicos');
		this.#btn_crear_periodo_academico = document.getElementById('btn-crear-periodo-academico');
		this.#form = document.getElementById('form-crear-periodo-academico');
		this.#btn_crear_periodo_academico.addEventListener('click', () => this.#crear_periodo_academico());
	}

	async init () {
		await this.#llenar_tabla();
	}

	async #crear_periodo_academico(){

		if (!this._validar_formulario(this.#form)) {
			this._notificacion('debe llenar el formulario', 'Error');
			return;
		}

     	const data = Object.fromEntries(new FormData(this.#form));
     	this._limpiar_objeto(data);

     	if (data.nombre === ''){
     		this._notificacion('Debe llenar todos los campos', 'Error');
     		return;
     	} 

		this.#btn_crear_periodo_academico.disabled = true;
		
     	const { creado } = await this._enviar_datos('./api.php?controller=academicos_controller&action=crear_periodo_academico', data);


		if (creado) {
			this.#llenar_tabla();
			this.#form.reset();
		} 

		this.#btn_crear_periodo_academico.disabled = false;

	}

	async #llenar_tabla() {
		const {periodos_academicos} = await this._traer_datos('./api.php?controller=academicos_controller&action=obtener_periodos_academicos');
		
		if (periodos_academicos) {
			
			let tbody = '';
			periodos_academicos.forEach(p => {
				tbody += `
	       			<tr class="border-bottom border-2">
	                    <td class="ps-3 py-2 fw-bold">${p.nombre}</td>
	                    <td class="text-center py-2">
							<span title="${p.estado}" data-id="${p.id}" class="btn-alternar-estado-periodo-academico badge ${p.estado !== 'Activo'? 'bg-light opacity-50 text-muted': 'bg-primary-subtle text-primary'} border border-primary-subtle small px-2 py-1 rounded-pill badge-vinculacion user-select-none" style="cursor: pointer;">${p.estado}</span>
						</td>
	                    <td class="text-end pe-3 py-2">
		                    <button type="button" data-id="${p.id}" title="Eliminar" class="btn-eliminar-periodo-academico btn btn-outline-danger btn-sm py-0 px-2 rounded-2" ><i class="bi bi-trash-fill fs-7"></i></button>
		                </td>
					</tr>
				`
			});
			this.#tbody.innerHTML = tbody;
			document.querySelectorAll('.btn-eliminar-periodo-academico').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const id = e.currentTarget.getAttribute('data-id');
					this.#eliminar_periodo_academico(id);
				})
			});
			document.querySelectorAll('.btn-alternar-estado-periodo-academico').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const id = e.currentTarget.getAttribute('data-id');
					this.#alternar_estado_periodo_academico(id);
				})
			});

		}
	}

	async #eliminar_periodo_academico(id) {
		const { eliminado } = await this._enviar_datos('./api.php?controller=academicos_controller&action=eliminar_periodo_academico', {'id': id}); 
		if (eliminado) {
			this.#llenar_tabla();
		} 
	}
	async #alternar_estado_periodo_academico(id) {
		const {alternado} = await this._enviar_datos('./api.php?controller=academicos_controller&action=alternar_estado_periodo_academico', {'id': id}); 
		if (alternado) {
			this.#llenar_tabla();
		} 

	}
}
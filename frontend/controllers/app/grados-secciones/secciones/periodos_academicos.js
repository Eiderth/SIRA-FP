import Utils from "../../../../core/utils.js";


export default class Periodos_academicos_controller extends Utils {
	#form;
	#btn;
	#tbody;

	constructor(){
		super();
		this.#tbody = document.getElementById('tbody-periodos-academicos');
		this.#btn = document.getElementById('btn-crear-periodo-academico');
		this.#form = document.getElementById('form-crear-periodo-academico');
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

         	const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=crear_periodo_academico', data);

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
		const resp = await this._traer_datos('./api.php?controller=grados_secciones_controller&action=obtener_periodos_academicos');
		let tbody = '';
		resp.periodos_academicos.forEach(p => {
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

	async #eliminar_periodo_academico(id) {
		const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=eliminar_periodo_academico', {'id': id}); 
		if (resp.estado != 'completado') {
			this._notificacion(resp.mensaje);
			return;
		} 
		this.#llenar_tabla();
	}
	async #alternar_estado_periodo_academico(id) {
		const resp = await this._enviar_datos('./api.php?controller=grados_secciones_controller&action=alternar_estado_periodo_academico', {'id': id}); 
		if (resp.estado != 'completado') {
			this._notificacion(resp.mensaje);
			return;
		} 
		this.#llenar_tabla();

	}
}
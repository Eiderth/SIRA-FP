import Utils from "../../../../core/utils.js";

export default class Seccion_3_controller extends Utils{

    #html;
    parametros_formulario;

    constructor(parametros_formulario) {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/secciones/seccion_3.html');
        this.parametros_formulario = parametros_formulario;
    }

    async init(data) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)
        this.#insertar_parametros();
        this.#dar_evento_select();
        if(data) this._llenar_inputs(data);
    }

    #insertar_parametros() { 
        const select_estado = document.getElementById('select-estado');
        const select_ciudad = document.getElementById('select-ciudad'); 

        this._llenar_select(select_estado, this.parametros_formulario.estados, 'Carabobo');
        const estado_id = this.parametros_formulario.estados.find(e=> e.nombre == 'Carabobo')?.id ?? null; 

        this._llenar_select(select_ciudad, this.parametros_formulario.ciudades.filter(c=> c.estado_id == estado_id), 'Valencia');
    }

    #dar_evento_select() {

        document.getElementById('select-estado')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const ciudades_filtradas = this.parametros_formulario.ciudades.filter(c => c.estado_id == id);

            this._llenar_select(document.getElementById('select-ciudad'), ciudades_filtradas, '');
        });

    }
}
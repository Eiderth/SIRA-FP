import Utils from "../../../../core/utils.js";

export default class Seccion_4_controller extends Utils{

    #html;
    parametros_formulario;

    constructor(parametros_formulario) {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/secciones/seccion_4.html');
        this.parametros_formulario = parametros_formulario;
    }

    async init(data) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)
        
        document.getElementById('info').classList.remove('d-none');
        document.getElementById('botonera').classList.replace('d-none','d-flex');
        
        this.#insertar_parametros();
        this.#dar_evento_select();
        if(data) this._llenar_inputs(data);
    }

    #insertar_parametros() { 
        const select_estado = document.getElementById('select-estado');
        const select_municipio = document.getElementById('select-municipio'); 
        const select_parroquia = document.getElementById('select-parroquia')

        this._llenar_select(select_estado, this.parametros_formulario.estados, 'Carabobo'); 

        this._llenar_select(select_municipio, this.parametros_formulario.municipios.filter(m=> m.estado_id == select_estado.value), 'Valencia');
    
        this._llenar_select(select_parroquia, this.parametros_formulario.parroquias.filter(p=> p.municipio_id == select_municipio.value), 'Valencia');

    }

    #dar_evento_select() {
        
        document.getElementById('select-estado')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_municipio = document.getElementById('select-municipio')
            const municipios_filtrados = this.parametros_formulario.municipios.filter(m => m.estado_id == id);

            this._llenar_select(select_municipio, municipios_filtrados, '');

            const parroquias_filtradas = this.parametros_formulario.parroquias.filter(p => p.municipio_id == select_municipio.value);

            this._llenar_select(document.getElementById('select-parroquia'), parroquias_filtradas, '');

        });

        document.getElementById('select-municipio')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_parroquia = document.getElementById('select-parroquia')
            const parroquias_filtradas = this.parametros_formulario.parroquias.filter(p => p.municipio_id == id);

            this._llenar_select(select_parroquia, parroquias_filtradas, '');

        });
    }
}
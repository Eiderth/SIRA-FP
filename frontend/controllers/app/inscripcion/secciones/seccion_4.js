import Utils from "../../../../core/utils.js";

export default class Seccion_4_controller extends Utils{

    #html;
    parametros_formulario;

    constructor(parametros_formulario) {
        super();
        this.#html = this._traer_html('./frontend/views/app/inscripcion/secciones/seccion_4.html');
        this.parametros_formulario = parametros_formulario;
    }

    async init() {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)
        
        document.getElementById('info').classList.remove('d-none');
        document.getElementById('botonera').classList.replace('d-none','d-flex');
        
        this.#insertar_parametros();
        this.#dar_evento_select();
        this.#dar_eventos_busqueda();
    }

    #insertar_parametros(estado = 'Carabobo', municipio = 'Valencia', parroquia = '') { 
    
        const select_estado = document.getElementById('select-estado');
        const select_municipio = document.getElementById('select-municipio'); 
        const select_parroquia = document.getElementById('select-parroquia')

        this._llenar_select(select_estado, this.parametros_formulario.estados, estado); 

        this._llenar_select(select_municipio, this.parametros_formulario.municipios.filter(c=> c.estado_id == select_estado.value), municipio);
        
        this._llenar_select(select_parroquia, this.parametros_formulario.parroquias.filter(p=> p.municipio_id == select_municipio.value), parroquia);

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


    #dar_eventos_busqueda() {
        let temp_cedula_identidad;
        document.getElementById('input-cedula-identidad-representante-secundario')?.addEventListener('input', (e) => {

            clearTimeout(temp_cedula_identidad);

            temp_cedula_identidad = setTimeout(async () => {
                const valor = e.target.value.trim();
                if(valor === '') return;
                const {representante, mensaje} = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=obtener_datos_representante', 
                {'cedula_identidad': valor});
    
                if (representante) {
                    const direccion = representante.direccion;
                    this._llenar_inputs({
                        ...representante.persona,
                        ...representante.persona_representante,
                        ...representante.direccion   
                    });

                    this.#insertar_parametros(
                        this.parametros_formulario.estados.find((e) => e.id == direccion.estado_id).nombre,
                        this.parametros_formulario.municipios.find((m) => m.id == direccion.municipio_id).nombre,
                        this.parametros_formulario.parroquias.find((p) => p.id == direccion.parroquia_id).nombre 
                    )
      
                    this._notificacion(mensaje, 'mensaje',  1000);
                }

            }, 1500);
        });
    }
}
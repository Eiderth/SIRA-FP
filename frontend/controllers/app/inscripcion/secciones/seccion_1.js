import Utils from "../../../../core/utils.js";

export default class Seccion_1_controller extends Utils{

    parametros_formulario;
    alterar_data
    #html;

    constructor(parametros_formulario, alterar_data) {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/secciones/seccion_1.html');
        this.parametros_formulario = parametros_formulario;
        this.alterar_data = alterar_data;
    }

    async init(data) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html);
        document.getElementById('btn-atras').classList.add('d-none');
        document.getElementById('info').classList.remove('d-none');
        document.getElementById('botonera').classList.replace('d-none','d-flex');
        this.#insertar_parametros();
        this.#dar_eventos_select();
        this.#dar_eventos_busqueda(data);

        
        if(data) this._llenar_inputs(data);

        const info = document.getElementById('info').classList.remove('d-none');
        const botones = document.getElementById('botonera').classList.replace('d-none','d-flex');
    }

    #insertar_parametros() { 
        const select_pais = document.getElementById('select-pais');
        const select_estado = document.getElementById('select-estado');
        const select_municipio  = document.getElementById('select-municipio');
        const select_ciudad = document.getElementById('select-ciudad');
        const select_parroquia = document.getElementById('select-parroquia');

        let estado_id; 
        let municipio_id; 

        if(select_pais) this._llenar_select(select_pais, this.parametros_formulario.paises, 'Venezuela'); 

        if(select_estado){
            this._llenar_select(select_estado, this.parametros_formulario.estados, 'Carabobo');
            estado_id = this.parametros_formulario.estados.find(e=> e.nombre == 'Carabobo')?.id ?? null;
        } 

        if(select_municipio){
            this._llenar_select(select_municipio, this.parametros_formulario.municipios.filter(m=> m.estado_id == estado_id), 'Libertador');
            municipio_id = this.parametros_formulario.municipios.find(m=> m.nombre == 'Libertador')?.id ?? null;
        } 

        if(select_ciudad) {
            this._llenar_select(select_ciudad, this.parametros_formulario.ciudades.filter(c=> c.estado_id == estado_id), 'Valencia');
        } 

        if(select_parroquia) {
            this._llenar_select(select_parroquia, this.parametros_formulario.parroquias.filter(p=> p.municipio_id == municipio_id), 'Rafael Urdaneta');
        } 

        const select_grado = document.getElementById('select-grado');
        const select_seccion = document.getElementById('select-seccion');
        const select_periodo = document.getElementById('select-periodo');

        if(select_grado) this._llenar_select(select_grado, this.parametros_formulario.grados, '8VO');
        if(select_seccion) this._llenar_select(select_seccion, this.parametros_formulario.secciones, 'A');
        if(select_periodo) this._llenar_select(select_periodo,this.parametros_formulario.periodos.reverse(), '');
    }

    #dar_eventos_select() {
        document.getElementById('select-pais')?.addEventListener('change', (e) => {
            const pais = e.target.options[e.target.selectedIndex].innerText;

            if(pais != 'Venezuela') {
                document.getElementById('select-estado').parentElement.classList.add('d-none');
                document.getElementById('select-municipio').parentElement.classList.add('d-none');
                document.getElementById('select-ciudad').parentElement.classList.add('d-none');
            } else {
                document.getElementById('select-estado').parentElement.classList.remove('d-none');
                document.getElementById('select-municipio').parentElement.classList.remove('d-none');
                document.getElementById('select-ciudad').parentElement.classList.remove('d-none');
            }
        });

        document.getElementById('select-estado')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const municipios_filtrados = this.parametros_formulario.municipios.filter(m => m.estado_id == id);
            const ciudades_filtradas = this.parametros_formulario.ciudades.filter(m => m.estado_id == id);

            this._llenar_select(document.getElementById('select-municipio'), municipios_filtrados, '');
            this._llenar_select(document.getElementById('select-ciudad'), ciudades_filtradas, '');
        });

        document.getElementById('select-municipio')?.addEventListener('change', (e) => {
            const select_parroquia = document.getElementById('select-parroquia');
            if (!select_parroquia) return;

            const id = e.target.value;
            const parroquias_filtradas = this.parametros_formulario.parroquias.filter(p => p.municipio_id == id);
            this._llenar_select(select_parroquia, parroquias_filtradas, '');
        });
    }

    #dar_eventos_busqueda(data) {
        let temp_cedula_identidad;
        document.getElementById('input-cedula-identidad-estudiante')?.addEventListener('input', (e) => {

            clearTimeout(temp_cedula_identidad);

            temp_cedula_identidad = setTimeout(async () => {
                const valor = e.target.value.trim();
                    if(valor === '' || Object.keys(data.estudiante).length != 0) return;

                    const resp = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=obtener_historial_estudiante', 
                    {'cedula_identidad': valor, 'cedula_escolar': null});

                    if(!resp.historial) return;
                    this._llenar_inputs(resp.historial.estudiante);
                    
                    this.alterar_data(resp.historial);

            }, 1500);
        });

        let temp_cedula_escolar;
        document.getElementById('input-cedula-escolar-estudiante')?.addEventListener('input', (e) => {

            clearTimeout(temp_cedula_escolar);

            temp_cedula_escolar = setTimeout(async () => {
                const valor = e.target.value.trim();
                if(valor === '' || Object.keys(data.estudiante).length != 0) return;

                const resp = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=obtener_historial_estudiante', 
                   {'cedula_identidad': null, 'cedula_escolar': valor});

                if(!resp.historial) return;

                this._llenar_inputs(resp.historial.estudiante);
                this.alterar_data(resp.historial);

            }, 1500);
        });
    }
}
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
        const select_parroquia = document.getElementById('select-parroquia');

        this._llenar_select(select_pais, this.parametros_formulario.paises, 'Venezuela'); 

        this._llenar_select(select_estado, this.parametros_formulario.estados, 'Carabobo');
        
        this._llenar_select(select_municipio, this.parametros_formulario.municipios.filter(m=> m.estado_id == select_estado.value), 'Libertador');
    
        this._llenar_select(select_parroquia, this.parametros_formulario.parroquias.filter(p=> p.municipio_id == select_municipio.value), 'Rafael Urdaneta');
        
        const select_nivel_academico = document.getElementById('select-nivel-academico');
        const select_grado = document.getElementById('select-grado');
        const select_seccion = document.getElementById('select-seccion');
        const select_periodo = document.getElementById('select-periodo');

        this._llenar_select(select_periodo, this.parametros_formulario.periodos, '');

        this._llenar_select(select_nivel_academico, this.parametros_formulario.niveles_academicos, 'Media General')

        const grados_secciones_filtradas = this.parametros_formulario.grados_secciones.filter((gs) => gs.nivel_academico_id == select_nivel_academico.value);
        const grados_filtrados = [...new Set(grados_secciones_filtradas.map((gs) => gs.grado_id))];
        
        this._llenar_select(select_grado, this.parametros_formulario.grados.filter((g) => grados_filtrados.includes(g.id) ), '1er año')

        const secciones_filtradas = this.parametros_formulario.grados_secciones.filter((gs) => gs.grado_id == select_grado.value).map((gs) => gs.seccion_id);
        
        this._llenar_select(select_seccion, this.parametros_formulario.secciones.filter((s) => secciones_filtradas.includes(s.id) ), 'A')

    }

    #dar_eventos_select() {
        document.getElementById('select-pais')?.addEventListener('change', (e) => {
            const pais = e.target.options[e.target.selectedIndex].innerText;

            if(pais != 'Venezuela') {
                document.getElementById('select-estado').parentElement.classList.add('d-none');
                document.getElementById('select-municipio').parentElement.classList.add('d-none');
                document.getElementById('select-parroquia').parentElement.classList.add('d-none');
            } else {
                document.getElementById('select-estado').parentElement.classList.remove('d-none');
                document.getElementById('select-municipio').parentElement.classList.remove('d-none');
                document.getElementById('select-parroquia').parentElement.classList.remove('d-none');
            }
        });

        document.getElementById('select-estado')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_municipio = document.getElementById('select-municipio');
            const select_parroquia = document.getElementById('select-parroquia');

            const municipios_filtrados = this.parametros_formulario.municipios.filter(m => m.estado_id == id);
            this._llenar_select(select_municipio, municipios_filtrados, '');

            const parroquias_filtradas = this.parametros_formulario.parroquias.filter(p => p.municipio_id == select_municipio.value);
            this._llenar_select(select_parroquia, parroquias_filtradas, '');
        });

        document.getElementById('select-municipio')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_parroquia = document.getElementById('select-parroquia');
            const parroquias_filtradas = this.parametros_formulario.parroquias.filter(p => p.municipio_id == id);
            this._llenar_select(select_parroquia, parroquias_filtradas, '');
        });

        document.getElementById('select-nivel-academico')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_grado = document.getElementById('select-grado');
            const grados_filtrados = [...new Set(this.parametros_formulario.grados_secciones.filter(gs => gs.nivel_academico_id == id).map(gs => gs.grado_id))] ;
            this._llenar_select(select_grado, this.parametros_formulario.grados.filter(g => grados_filtrados.includes(g.id)) , '');

            const select_seccion = document.getElementById('select-seccion');
            const secciones_filtradas = [...new Set(this.parametros_formulario.grados_secciones.filter(gs => gs.grado_id == select_grado.value).map(gs => gs.seccion_id))] ;
            this._llenar_select(select_seccion, this.parametros_formulario.secciones.filter(s => secciones_filtradas.includes(s.id)) , '');
        });

        document.getElementById('select-grado')?.addEventListener('change', (e) => {
            const id = e.target.value;
            const select_seccion = document.getElementById('select-seccion');
            const secciones_filtradas = [...new Set(this.parametros_formulario.grados_secciones.filter(gs => gs.grado_id == id).map(gs => gs.seccion_id))] ;
            this._llenar_select(select_seccion, this.parametros_formulario.secciones.filter(s => secciones_filtradas.includes(s.id)) , '');
        });
    }

    #dar_eventos_busqueda(data) {
        let temp_cedula_identidad;
        document.getElementById('input-cedula-identidad-estudiante')?.addEventListener('input', (e) => {

            clearTimeout(temp_cedula_identidad);

            temp_cedula_identidad = setTimeout(async () => {
                const valor = e.target.value.trim();
                    if(valor === '' || Object.keys(data).length != 0) return;
                    const resp = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=obtener_historial_estudiante', 
                    {'cedula_identidad': valor});

                    if(!resp.historial) return;
                    this._llenar_inputs({...resp.historial.estudiante.persona, ...resp.historial.estudiante.persona_estudiante, ...resp.historial.inscripcion});
                    
                    
                    this.alterar_data(resp.historial);

            }, 1500);
        });
    }
}
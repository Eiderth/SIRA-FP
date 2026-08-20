import Utils from "../../../core/utils.js"

export default class Dashboard_controller extends Utils {
    
    #html;
    _cambiar_interfaz
 
    constructor(cambiar_interfaz) {
        super();
        this.#html = this._traer_html('./frontend/views/app/dashboard/index.html');
        this._cambiar_interfaz = cambiar_interfaz;
    }

    async init() {
        const html = await this.#html;
        
        await this._inyectar_html( document.getElementById('root-app'), html);
        const usuario = JSON.parse(localStorage.getItem('sesion'));
        this.#llenar_data_usuario(usuario);
        this.#llenar_cards();
        this.#dar_eventos_click();

    }

    #llenar_data_usuario(usuario) {

        document.getElementById('info-letra-usuario').textContent = usuario.nombre.charAt(0)
        document.getElementById('info-usuario').textContent = usuario.nombre
        document.getElementById('info-rol').textContent = usuario.rol
    }

    async #llenar_cards() {
        const resp = await this._traer_datos('./api.php?controller=dashboard_controller&action=obtener_datos_resumen')
        // console.log(resp)
        document.getElementById('estudiantes-activos').textContent = resp.data.estudiantes_activos;
        document.getElementById('periodo-activo').textContent = resp.data.periodo_activo;
        document.getElementById('grados-secciones-activas').textContent = resp.data.grados_secciones_activas;
        if (resp.resumen_reportes) return;

        let reportes = ''
        resp.data.resumen_reportes.forEach(r => {
            reportes += `
                <tr>
                    <th class="ps-4">${r.nombre_1} ${r.apellido_1}</th>
                    <th>${r.cedula_identidad !== ''? (r.nacionalidad+r.cedula_identidad): r.cedula_escolar}</th>
                    <th>${r.grado} ${r.seccion}</th>
                    <th>${r.fecha_inscripcion}</th>
                </tr>
            `    
        })
        document.getElementById('resumen-reportes').innerHTML = reportes;
    }

    #dar_eventos_click(){
        document.getElementById('cont-estudiantes-activos').addEventListener('click', () => {
            this._cambiar_interfaz('buscar');
        });
        document.getElementById('cont-periodo-activo').addEventListener('click', () => {
            this._cambiar_interfaz('periodos');
        });
        document.getElementById('cont-grados-secciones-activas').addEventListener('click', () => {
            this._cambiar_interfaz('grados_secciones');
        });
        document.getElementById('btn-table-reportes').addEventListener('click', () => {
            this._cambiar_interfaz('reportes');
        });
    }
}
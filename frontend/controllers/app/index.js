import Utils from "../../core/utils.js";
import Inscripcion_controller from "./inscripcion/index.js";
import Dashboard_controller from "./dashboard/index.js";
import Reportes_controller from "./reportes/index.js";
import Usuarios_controller from "./usuarios/index.js";


export default class App_controller extends Utils {
    #root;
    #html;

    #dashboard_controller;
    #inscripcion_controller;
    #reportes_controller;
    #usuarios_controller;
    
    cambiar_interfaz;
    
    #seccion_app;

    constructor(cambiar_interfaz) {
        super();
        this.#html = this._traer_html('./frontend/views/app/index.html');
        this.cambiar_interfaz = cambiar_interfaz;
        this.#root = document.getElementById('root'); 

        this.#dashboard_controller = new Dashboard_controller((interfaz) =>this._cambiar_interfaz_app(interfaz));
        this.#inscripcion_controller = new Inscripcion_controller();
        this.#reportes_controller = new Reportes_controller();
        this.#usuarios_controller = new Usuarios_controller();

    }

    async init(){
        const html = await this.#html;
        await this._inyectar_html(this.#root, html); 

        this.#dar_permiso_admin(JSON.parse(localStorage.getItem('sesion')));
        document.getElementById('btn-registro').addEventListener('click', () => this.#iniciar_inscripcion());
        document.getElementById('btn-reporte').addEventListener('click', () => this.#iniciar_reportes());
        document.getElementById('btn-dashboard').addEventListener('click', () => this.#iniciar_dashboard());
        document.getElementById('btn-usuarios').addEventListener('click', () => this.#iniciar_usuarios());
        document.getElementById('btn-salir').addEventListener('click', () => this.#cerrar_sesion());


        this.#dashboard_controller.init();
        this.#seccion_app = 'dashboard';

    }

    async #iniciar_inscripcion() {
        if (this.#seccion_app == 'registro') return;

        this.#inscripcion_controller.init();

        this.#seccion_app = 'registro';
    }

    async #iniciar_reportes(){
        if (this.#seccion_app == 'reportes') return;

        this.#reportes_controller.init();
        
        this.#seccion_app = 'reportes';
    }

    async #iniciar_dashboard() {
        if (this.#seccion_app == 'dashboard') return;

        this.#dashboard_controller.init();
        
        this.#seccion_app = 'dashboard';
    }

    async #iniciar_usuarios() {
        if (this.#seccion_app == 'gestion_usuarios') return;

        this.#usuarios_controller.init();
        
        this.#seccion_app = 'usuarios';
    }

    #dar_permiso_admin(token_sesion){
        if(token_sesion.rol === 'Administrador'){
            document.getElementById('btn-usuarios').classList.remove('d-none');
        }
    }

    async _cambiar_interfaz_app(interfaz){

        switch(interfaz) {
            case 'buscar':
                this._notificacion('buscar')
                this.#seccion_app = 'buscar';
                break;
            case 'periodos':
                this._notificacion('periodos')
                this.#seccion_app = 'periodos';
                break;
            case 'reportes':
                this.#iniciar_reportes()
                break;
            case 'grados_secciones':
                this._notificacion('grados_secciones')
                this.#seccion_app = 'grados_secciones';
                break;

            default: this._notificacion('esa interfaz no existe');
        }
    }


    async #cerrar_sesion(){
        const resp = await this._traer_datos('./api.php?controller=login_controller&action=cerrar_sesion');
        if (resp) {
            this._notificacion(resp.mensaje);
            localStorage.clear();
            this.cambiar_interfaz('login');
        }
    }
}